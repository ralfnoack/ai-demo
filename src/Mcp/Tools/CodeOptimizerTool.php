<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use Composer\Pcre\Preg;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

final readonly class CodeOptimizerTool
{
    public const string RECTOR_LEVEL_FILE_DEFAULT = <<<'PHP'
                     <?php

                     declare(strict_types=1);

                     const TYPE_COVERAGE_LEVEL = 0;
                     const DEAD_CODE_LEVEL = 0;
                     const CODE_QUALITY_LEVEL = 0;
                     PHP;

    public function __construct(private ?string $pwd = null)
    {
        if (null !== $this->pwd) {
            chdir($this->pwd);
        }
    }

    public const string CONSTANT_NAME = 'CODE_QUALITY_LEVEL';

    public const string FILEPATH = __DIR__.'/../../../rector_levels.php';

    public function run(): bool
    {
        $codeInspectionTool = new CodeInspectionTool();

        while ($needToOptimize = $this->levelAreNotMaximal(
            $codeQualityLevel = $this->readOrWriteRectorLevel(RectorLevelEnum::CODE_QUALITY_LEVEL),
            $deadCodeLevel = $this->readOrWriteRectorLevel(RectorLevelEnum::DEAD_CODE_LEVEL),
            $typeCoverageLevel = $this->readOrWriteRectorLevel(RectorLevelEnum::TYPE_COVERAGE_LEVEL),
            $codingStyleLevel = $this->readOrWriteRectorLevel(RectorLevelEnum::CODING_STYLE_LEVEL),
        ) && $this->testRunSuccessful()) {
            $numberOfOptimizedFiles = $codeInspectionTool->numberOfRectorOptimizedFiles();
            if (0 === $numberOfOptimizedFiles) {
                $this->incrementedOneRectorLevel($codeQualityLevel, $deadCodeLevel, $typeCoverageLevel, $codingStyleLevel);
                continue;
            }

            $codeInspectionTool->rectorList();
            if (!$this->testRunSuccessful()) {
                return false;
            }

            $commitChangesTool = new CommitChangesTool();
            if ($commitChangesTool->isCommitable($this->pwd)) {
                $commitChangesTool->commitChanges(
                    $this->pwd,
                    'Rector Auto Optimize applied changes '.$codeQualityLevel,
                );
            }

            $incremented = $this->incrementedOneRectorLevel($codeQualityLevel, $deadCodeLevel, $typeCoverageLevel, $codingStyleLevel);
            if (!$incremented) {
                break;
            }
        }

        if (false === $needToOptimize) {
            echo "All Rector levels are at their maximum values.\n";
        }

        return true;
    }

    private function testRunSuccessful(): bool
    {
        return new CodeInspectionTool()->pestTest();
    }

    private function incrRectorLevelIfPossible(RectorLevelEnum $rectorLevelEnum, int $current, int $maxIncr = 10): bool
    {
        if ($current >= $rectorLevelEnum->getMaxLevel()) {
            return false;
        }

        $increment = min($maxIncr, $rectorLevelEnum->getMaxLevel() - $current);
        echo 'Incrementing '.$rectorLevelEnum->getConstantName().sprintf(' = %d by %d%s', $current, $increment, PHP_EOL);
        $this->incrementRectorLevel($rectorLevelEnum, $increment);

        return true;
    }

    private function incrementRectorLevel(RectorLevelEnum $rectorLevelEnum, int $incrementBy = 1): void
    {
        $this->incrementConstanstInPhpFile(
            self::FILEPATH,
            $rectorLevelEnum->getConstantName(),
            $incrementBy,
        );
    }

    private function incrementConstanstInPhpFile(string $filePath, string $constantName, int $incrementBy = 1): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException('Could not read file: ' . $filePath);
        }

        $pattern = sprintf('/(const\s+%s\s*=\s*)(\d+)(\s*;)/', preg_quote($constantName, '/'));
        $newContent = Preg::replaceCallback(
            $pattern,
            function (array $matches) use ($incrementBy): string {
                $newValue = (int) $matches[2] + $incrementBy;

                return $matches[1].$newValue.$matches[3];
            },
            $content,
        );
        file_put_contents($filePath, $newContent);
    }

    private function readConstantFromPhpFile(string $filePath, string $constantName): int
    {
        $fs = new Filesystem();
        if (!$fs->exists($filePath)) {
            throw new FileNotFoundException('File does not exist: ' . $filePath);
        }

        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new IOException('File is not readable: ' . $filePath);
        }

        $pattern = sprintf('/(const\s+%s\s*=\s*)(\d+)(\s*;)/', preg_quote($constantName, '/'));
        if (Preg::isMatch($pattern, $content, $matches)) {
            return (int) $matches[2];
        }

        throw new \RuntimeException(sprintf('Constant %s not found in file: %s', $constantName, $filePath));
    }

    private function writeConstantToPhpFile(string $filePath, string $constantName, int $value = 0): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException('Could not read file: ' . $filePath);
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
            throw new \RuntimeException(sprintf('Could not read constant %s from file: ', $constantName).self::FILEPATH);
        } catch (\RuntimeException) {
            $this->writeConstantToPhpFile(self::FILEPATH, $constantName, 0);
        }

        return 0;
    }

    public function incrementedOneRectorLevel(int $codeQualityLevel, int $deadCodeLevel, int $typeCoverageLevel, int $codingStyleLevel): bool
    {
        $incremented = false;
        $incremented = $incremented || $this->incrRectorLevelIfPossible(
            RectorLevelEnum::CODE_QUALITY_LEVEL,
            $codeQualityLevel,
        );
        $incremented = $incremented || $this->incrRectorLevelIfPossible(
            RectorLevelEnum::DEAD_CODE_LEVEL,
            $deadCodeLevel,
        );

        $incremented = $incremented || $this->incrRectorLevelIfPossible(
            RectorLevelEnum::TYPE_COVERAGE_LEVEL,
            $typeCoverageLevel,
        );

        return $incremented || $this->incrRectorLevelIfPossible(
            RectorLevelEnum::CODING_STYLE_LEVEL,
            $codingStyleLevel,
        );
    }

    private function levelAreNotMaximal(int $codeQualityLevel, int $deadCodeLevel, int $typeCoverageLevel, int $codingStyleLevel): bool
    {
        return $codeQualityLevel < RectorLevelEnum::CODE_QUALITY_LEVEL->getMaxLevel()
            || $deadCodeLevel < RectorLevelEnum::DEAD_CODE_LEVEL->getMaxLevel()
            || $typeCoverageLevel < RectorLevelEnum::TYPE_COVERAGE_LEVEL->getMaxLevel()
            || $codingStyleLevel < RectorLevelEnum::CODING_STYLE_LEVEL->getMaxLevel();
    }
}
