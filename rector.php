<?php

declare(strict_types=1);

require __DIR__.'/rector_levels.php';

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
    ->withCodingStyleLevel(CODING_STYLE_LEVEL)
    ->withTypeCoverageLevel(TYPE_COVERAGE_LEVEL)
    ->withDeadCodeLevel(DEAD_CODE_LEVEL)
    ->withCodeQualityLevel(CODE_QUALITY_LEVEL);
