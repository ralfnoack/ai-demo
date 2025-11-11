<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use Composer\Pcre\Preg;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

final readonly class CodeOptimizerTool
{
    const string RECTOR_LEVEL_FILE_DEFAULT = <<<'PHP'
                     <?php

                     declare(strict_types=1);

                     const TYPE_COVERAGE_LEVEL = 0;
                     const TYPE_COVERAGE_LEVEL_MAX = 62;
                     const DEAD_CODE_LEVEL = 0;
                     const DEAD_CODE_LEVEL_MAX = 55;
                     const CODE_QUALITY_LEVEL = 0;
                     const CODE_QUALITY_LEVEL_MAX = 77;
                     PHP;

    public function __construct(private ?string $pwd = null)
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

        while ($this->testRunSuccessful()) {
            $codeQualityLevel = $this->readOrWriteRectorLevel(RectorLevelEnum::CODE_QUALITY_LEVEL);
            $deadCodeLevel = $this->readOrWriteRectorLevel(RectorLevelEnum::DEAD_CODE_LEVEL);
            $typeCoverageLevel = $this->readOrWriteRectorLevel(RectorLevelEnum::TYPE_COVERAGE_LEVEL);

            $optimize = $codeInspectionTool->rectorOptimize();
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

            if ($codeQualityLevel < RectorLevelEnum::CODE_QUALITY_LEVEL->getMaxLevel()) {
                $this->incrementConstanstInPhpFile(
                    self::FILEPATH,
                    RectorLevelEnum::CODE_QUALITY_LEVEL->getConstantName(),
                );
            } elseif ($deadCodeLevel < RectorLevelEnum::DEAD_CODE_LEVEL->getMaxLevel()) {
                $this->incrementConstanstInPhpFile(self::FILEPATH, RectorLevelEnum::DEAD_CODE_LEVEL->getConstantName());
            } elseif ($typeCoverageLevel < RectorLevelEnum::TYPE_COVERAGE_LEVEL->getMaxLevel()) {
                $this->incrementConstanstInPhpFile(
                    self::FILEPATH,
                    RectorLevelEnum::TYPE_COVERAGE_LEVEL->getConstantName(),
                );
            } else {
                break;
            }
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
            function ($matches) use ($incrementBy): string {
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

    private function readConstantFromPhpFile(string $filePath, string $constantName): int
    {
        $fs = new Filesystem();
        if (!$fs->exists($filePath)) {
            throw new FileNotFoundException("File does not exist: $filePath");
        }
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new IOException("File is not readable: $filePath");
        }

        $pattern = sprintf('/(const\s+%s\s*=\s*)(\d+)(\s*;)/', preg_quote($constantName, '/'));
        if (Preg::isMatch($pattern, $content, $matches)) {
            return (int) $matches[2];
        }

        throw new \RuntimeException("Constant $constantName not found in file: $filePath");
    }

    private function writeConstantToPhpFile(string $filePath, string $constantName, int $value = 0): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException("Could not read file: $filePath");
        }

        $pattern = sprintf('/(const\s+%s\s*=\s*)(\d+)(\s*;)/', preg_quote($constantName, '/'));
        $newContent = Preg::replace($pattern, sprintf('$1%d$3', $value), $content);

        file_put_contents($filePath, $newContent);
    }

    public function readOrWriteRectorLevel(RectorLevelEnum $rectorLevelEnum): int
    {
        $constantName = $rectorLevelEnum->getConstantName();

        try {
            return $this->readConstantFromPhpFile(self::FILEPATH, $constantName);
        } catch (FileNotFoundException) {
            file_put_contents(self::FILEPATH, self::RECTOR_LEVEL_FILE_DEFAULT);
        } catch (IOException) {
            throw new \RuntimeException("Could not read constant $constantName from file: ".self::FILEPATH);
        } catch (\RuntimeException) {
            $this->writeConstantToPhpFile(self::FILEPATH, $constantName, 0);
        }

        return 0;
    }
}
