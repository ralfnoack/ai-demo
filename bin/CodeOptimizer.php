<?php

declare(strict_types=1);

namespace Tests\Mcp\Tools;

use App\Mcp\Tools\CodeOptimizerTool;

$tool = new CodeOptimizerTool();
$path = __DIR__.'/../../../src';

$result = $tool->run($path);
