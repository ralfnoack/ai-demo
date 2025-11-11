<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Mcp\Capability\Attribute\McpPrompt;

class OptimizationPrompt
{
    /**
     * Generiert einen Optimierungsvorschlag basierend auf einem Analyse-Report.
     *
     * @param array $report Analyse-Report
     */
    #[McpPrompt(name: 'optimize_code')]
    public function optimizeCode(array $report): array
    {
        return [
            [
                'role' => 'assistant',
                'content' => 'Hier sind Optimierungsvorschläge basierend auf dem Analyse-Report:',
            ],
            [
                'role' => 'user',
                'content' => json_encode($report, JSON_PRETTY_PRINT),
            ],
        ];
    }
}
