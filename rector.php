<?php

declare(strict_types=1);

const TYPE_COVERAGE_LEVEL = 26;
const DEAD_CODE_LEVEL = 0;
const CODE_QUALITY_LEVEL = 62;

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/assets',
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withTypeCoverageLevel(TYPE_COVERAGE_LEVEL)
    ->withDeadCodeLevel(DEAD_CODE_LEVEL)
    ->withCodeQualityLevel(CODE_QUALITY_LEVEL);
