<?php
declare(strict_types=1);

namespace App\Mcp\Resources;

use Mcp\Capability\Attribute\McpResource;

class InspectionReportResource
{
    /**
     * Stellt einen statischen Analyse-Report bereit.
     * @param array $report Analyse-Report
     * @return array
     */
    #[McpResource(uri: 'report://inspection', name: 'inspection_report', mimeType: 'application/json')]
    public function getReport(array $report): array
    {
        return $report;
    }
}

