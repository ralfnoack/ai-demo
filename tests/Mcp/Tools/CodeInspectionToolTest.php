<?php

use App\Mcp\Tools\CodeInspectionTool;

$tool = new CodeInspectionTool();
$path = __DIR__.'/../../../src';

beforeEach(function (): void {});

/*test('rector analyze', function () use ($tool, $path) {
    $result = $tool->rectorAnalyze($path);
    expect($result)
        ->toBeArray()
        ->toHaveKey('totals');
});

test('phpstan analyze', function () use ($tool, $path) {
    $result = $tool->phpstanAnalyze($path);
    expect($result)
        ->toBeArray()
        ->toHaveKey('totals');
});
*/
// test('rector optimize', function () use ($tool, $path) {
//    $result = $tool->rectorOptimize($path);
//    expect($result)
//        ->toBeArray()
//        ->toHaveKey('totals');
// });
