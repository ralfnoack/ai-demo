<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

final readonly class CodeInspectionTool
{
    public function __construct(private ?string $pwd = '.')
    {
        if (null !== $this->pwd) {
            chdir($this->pwd);
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
//        echo " result: $returnVar\n";
//        echo ' output: '.implode("\n", $output)."\n";

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
        $cmd = sprintf('php vendor/bin/rector process --output-format json', escapeshellarg($path));

        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
        echo " result: $returnVar\n";
        echo ' output: '.implode("\n", $output)."\n";

        $json = implode("\n", $output);

        return json_decode($json, true) ?? ['error' => 'rector Optimierung fehlgeschlagen'];
    }

    #[McpTool(name: 'rector_listrules')]
    public function rectorList(): array
    {
        $output = [];

        $cmd = sprintf('php vendor/bin/rector list-rules');

        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
//        echo " result: $returnVar\n";
//        echo ' output: '.implode("\n", $output)."\n";
        file_put_contents($this->pwd . '/docs/rector-rules.md', implode("\n", $output));

        $output = [];
        $cmd = sprintf('php vendor/bin/rector list-rules --output-format json');

        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
//        echo " result: $returnVar\n";
//        echo ' output: '.implode("\n", $output)."\n";

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
    public function pestTest(string $path = '/tests'): bool
    {
        $output = [];
        $cmd = sprintf('php -dxdebug.mode=coverage %s --testdox-text %s --coverage-html %s --path-coverage %s',
            escapeshellarg($this->pwd . '/vendor/bin/pest'),
            escapeshellarg($this->pwd . '/docs/testdox.txt'),
            escapeshellarg($this->pwd . '/docs/coverage'),
            escapeshellarg($this->pwd . $path)
        );


        echo "$cmd\n";
        exec($cmd, $output, $returnVar);
        echo " result: $returnVar\n";
        echo ' output: '.implode("\n", $output)."\n";

        $json = implode("\n", $output);

        return $returnVar === 0;
    }
}
