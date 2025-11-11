<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

enum RectorLevelEnum
{
    case TYPE_COVERAGE_LEVEL;
    case DEAD_CODE_LEVEL;
    case CODE_QUALITY_LEVEL;
    case CODING_STYLE_LEVEL;

    public function getMaxLevel(): int
    {
        return match ($this) {
            self::TYPE_COVERAGE_LEVEL => 62,
            self::DEAD_CODE_LEVEL => 55,
            self::CODE_QUALITY_LEVEL => 77,
            self::CODING_STYLE_LEVEL => 1000,
        };
    }

    public function getConstantName(): string
    {
        return match ($this) {
            self::TYPE_COVERAGE_LEVEL => 'TYPE_COVERAGE_LEVEL',
            self::DEAD_CODE_LEVEL => 'DEAD_CODE_LEVEL',
            self::CODE_QUALITY_LEVEL => 'CODE_QUALITY_LEVEL',
            self::CODING_STYLE_LEVEL => 'CODING_STYLE_LEVEL',
        };
    }
}
