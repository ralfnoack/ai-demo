<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/vendor/autoload.php';

use App\Mcp\Tools\CodeOptimizerTool;

$tool = new CodeOptimizerTool(dirname(__DIR__));
$path = dirname(__DIR__).'/src';

$result = $tool->run($path);
