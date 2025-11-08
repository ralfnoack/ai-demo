<?php
// ...existing code...
namespace App\Tests\Mcp\Tools;

use PHPUnit\Framework\TestCase;
use App\Mcp\Tools\CodeInspectionTool;

class CodeInspectionToolTest extends TestCase
{
    private CodeInspectionTool $tool;

    protected function setUp(): void
    {
        $this->tool = new CodeInspectionTool();
    }

    public function testRectorAnalyze(): void
    {
        $result = $this->tool->rectorAnalyze(__DIR__ . '/../src');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('totals', $result);
    }

    public function testPhpstanAnalyze(): void
    {
        $result = $this->tool->phpstanAnalyze(__DIR__ . '/../src');
        $this->assertIsArray($result);

        $this->assertArrayHasKey('totals', $result);
    }

    public function testRectorOptimize(): void
    {
        $result = $this->tool->rectorOptimize(__DIR__ . '/../src');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('totals', $result);
    }
}
// ...existing code...

