<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

final class CodeInspectionTool
{
    public function __construct($pwd = null)
    {
        if (null !== $pwd) {
            chdir($pwd);
        }
    }

    /**
     * Führt eine rector Analyse aus und gibt das Ergebnis als Array zurück.
     *
     * @param string $path Pfad zum zu analysierenden Code
     *
     * @return array Analyse-Report
     */
    #[McpTool(name: 'rector_analyze')]
    public function rectorAnalyze(string $path): array
    {
        $output = [];
        $cmd = sprintf('php vendor/bin/rector process %s --dry-run --output-format json', escapeshellarg($path));

        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
        echo " result: $returnVar\n";
        echo ' output: '.implode("\n", $output)."\n";

        $json = implode("\n", $output);

        return json_decode($json, true) ?? ['error' => 'rector Analyse fehlgeschlagen'];
    }

    /**
     * Führt eine rector Optimierung aus und gibt das Ergebnis als Array zurück.
     *
     * @param string $path Pfad zum zu optimierenden Code
     *
     * @return array Optimierungs-Report
     */
    #[McpTool(name: 'rector_optimize')]
    public function rectorOptimize(string $path): array
    {
        $output = [];
        $cmd = sprintf('php vendor/bin/rector process %s --output-format json', escapeshellarg($path));

        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
        echo " result: $returnVar\n";
        echo ' output: '.implode("\n", $output)."\n";

        $json = implode("\n", $output);

        return json_decode($json, true) ?? ['error' => 'rector Optimierung fehlgeschlagen'];
    }

    /**
     * Führt eine phpstan Analyse aus und gibt das Ergebnis als Array zurück.
     *
     * @param string $path Pfad zum zu analysierenden Code
     *
     * @return array Analyse-Report
     */
    #[McpTool(name: 'phpstan_analyze')]
    public function phpstanAnalyze(string $path): array
    {
        $output = [];
        $cmd = sprintf('php vendor/bin/phpstan analyse %s --no-progress --error-format=json', escapeshellarg($path));

        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
        echo " result: $returnVar\n";
        echo ' output: '.implode("\n", $output)."\n";

        $json = implode("\n", $output);

        return json_decode($json, true) ?? ['error' => 'phpstan Analyse fehlgeschlagen'];
    }

    /**
     * Führt eine phpunit Testsuite aus und gibt das Ergebnis als Array zurück.
     *
     * @param string $path Pfad zum zu analysierenden Code
     *
     * @return array Analyse-Report
     */
    #[McpTool(name: 'phpunit_test')]
    public function phpunitTest(string $path): array
    {
        $output = [];
        $cmd = sprintf('php vendor/bin/phpunit %s', escapeshellarg($path));

        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
        echo " result: $returnVar\n";
        echo ' output: '.implode("\n", $output)."\n";

        $json = implode("\n", $output);

        return json_decode($json, true) ?? ['error' => 'phpunit Test fehlgeschlagen'];
    }

    /**
     * Führt eine phpunit Testsuite aus und gibt das Ergebnis als Array zurück.
     *
     * @param string $path Pfad zum zu analysierenden Code
     *
     * @return array Analyse-Report
     */
    #[McpTool(name: 'pest_test')]
    public function pestTest(string $path): array
    {
        $output = [];
        $cmd = sprintf('php vendor/bin/pest %s', escapeshellarg($path));

        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
        echo " result: $returnVar\n";
        echo ' output: '.implode("\n", $output)."\n";

        $json = implode("\n", $output);

        return json_decode($json, true) ?? ['error' => 'pest Test fehlgeschlagen'];
    }
}
