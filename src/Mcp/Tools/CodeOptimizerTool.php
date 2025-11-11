<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

final class CodeOptimizerTool
{
    public function __construct(private readonly ?string $pwd = null)
    {
        if (null !== $this->pwd) {
            chdir($this->pwd);
        }
    }

    public const string CONSTANT_NAME = 'CODE_QUALITY_LEVEL';
    public const string FILEPATH = __DIR__.'/../../../rector.php';

    public function run(string $path): bool
    {
        $codeInspectionTool = new CodeInspectionTool();

        $codeQualityLevel = 0;

        while ($this->testRunSuccessful()) {
            try {
                $codeQualityLevel = $this->readConstanstInPhpFile(self::FILEPATH, self::CONSTANT_NAME);
            } catch (\RuntimeException) {
                $this->writeConstanstInPhpFile(self::FILEPATH, self::CONSTANT_NAME, $codeQualityLevel);
            }
            $optimize = $codeInspectionTool->rectorList();
            $optimize = $codeInspectionTool->rectorOptimize($path);
            if (!$this->testRunSuccessful()) {
                return false;
            }
            $commitChangesTool = new CommitChangesTool();
            if ($commitChangesTool->isCommitable($this->pwd)) {
                $commit = $commitChangesTool->commitChanges(
                    $this->pwd,
                    'Rector Auto Optimize applied changes '.$codeQualityLevel,
                );
            }
            $this->incrementConstanstInPhpFile(self::FILEPATH, self::CONSTANT_NAME);
        }

        return true;
    }

    private function testRunSuccessful(): bool
    {
        $codeInspectionTool = new CodeInspectionTool();

        return $codeInspectionTool->pestTest();
    }

    private function incrementConstanstInPhpFile(string $filePath, string $constantName, int $incrementBy = 1): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException("Could not read file: $filePath");
        }

        $pattern = sprintf('/(const\s+%s\s*=\s*)(\d+)(\s*;)/', preg_quote($constantName, '/'));
        $newContent = preg_replace_callback(
            $pattern,
            function ($matches) use ($incrementBy) {
                $newValue = (int) $matches[2] + $incrementBy;

                return $matches[1].$newValue.$matches[3];
            },
            $content,
        );

        if (null === $newContent) {
            throw new \RuntimeException("Regex error while processing file: $filePath");
        }

        file_put_contents($filePath, $newContent);
    }

    private function readConstanstInPhpFile(string $filePath, string $constantName): int
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException("Could not read file: $filePath");
        }

        $pattern = sprintf('/(const\s+%s\s*=\s*)(\d+)(\s*;)/', preg_quote($constantName, '/'));
        if (preg_match($pattern, $content, $matches)) {
            return (int) $matches[2];
        }

        throw new \RuntimeException("Constant $constantName not found in file: $filePath");
    }

    private function writeConstanstInPhpFile(string $filePath, string $constantName, int $value): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException("Could not read file: $filePath");
        }

        $pattern = sprintf('/(const\s+%s\s*=\s*)(\d+)(\s*;)/', preg_quote($constantName, '/'));
        $newContent = preg_replace($pattern, sprintf('$1%d$3', $value), $content);

        if (null === $newContent) {
            throw new \RuntimeException("Regex error while processing file: $filePath");
        }

        file_put_contents($filePath, $newContent);
    }
}
