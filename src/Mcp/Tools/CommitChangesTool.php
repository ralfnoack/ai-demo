<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use Mcp\Capability\Attribute\McpTool;

final class CommitChangesTool
{
    public function isExecutableSvn(): bool
    {
        $output = [];
        exec('which svn', $output);

        return $output !== [];
    }

    public function isCommitable(string $path): bool
    {
        return $this->isCommitableGit($path) || $this->isCommitableSvn($path);
    }

    public function isCommitableGit(string $path): bool
    {
        return $this->isExecutableGit() && $this->isPathVersionControlledByGit($path);
    }

    public function isCommitableSvn(string $path): bool
    {
        return $this->isExecutableSvn() && $this->isPathVersionControlledBySvn($path);
    }

    public function isExecutableGit(): bool
    {
        $output = [];
        exec('which git', $output);

        return $output !== [];
    }

    public function isPathVersionControlledByGit(string $path): bool
    {
        $output = [];
        exec(sprintf('cd %s && git rev-parse --is-inside-work-tree', escapeshellarg($path)), $output);

        return $output !== [] && 'true' === trim($output[0]);
    }

    public function isPathVersionControlledBySvn(string $path): bool
    {
        $output = [];
        exec(sprintf('cd %s && svn info', escapeshellarg($path)), $output);

        return $output !== [];
    }

    /**
     * @return array<string, string>
     */
    public function commitChangesGit(string $path, string $message): array
    {
        $output = [];
        $returnVar = 0;
        $cmd = sprintf('git add %s', escapeshellarg($path));

        echo $cmd . PHP_EOL;
        exec($cmd, $output, $returnVar);
        echo sprintf(' result: %d%s', $returnVar, PHP_EOL);
        echo ' output: '.implode("\n", $output)."\n";

        $cmd = sprintf('git commit -m %s', escapeshellarg($message));

        echo $cmd . PHP_EOL;
        exec($cmd, $output, $returnVar);
        echo sprintf(' result: %d%s', $returnVar, PHP_EOL);
        echo ' output: '.implode("\n", $output)."\n";

        if (0 !== $returnVar) {
            return ['error' => 'svn commit fehlgeschlagen mit Fehlercode '.$returnVar];
        }

        return ['success' => implode("\n", $output)];
    }

    public function commitChangesSvn(string $path, string $message): array
    {
        $output = [];
        $returnVar = 0;
        $cmd = sprintf('svn commit  %s -m %s', escapeshellarg($path), escapeshellarg($message));

        echo $cmd . PHP_EOL;
        exec($cmd, $output, $returnVar);
        echo sprintf(' result: %d%s', $returnVar, PHP_EOL);
        echo ' output: '.implode("\n", $output)."\n";

        if (0 !== $returnVar) {
            return ['error' => 'svn commit fehlgeschlagen mit Fehlercode '.$returnVar];
        }

        return ['success' => implode("\n", $output)];
    }

    #[McpTool(name: 'commit_changes')]
    public function commitChanges(string $path, string $message): array
    {
        if ($this->isCommitableGit($path)) {
            return $this->commitChangesGit($path, $message);
        }

        if ($this->isCommitableSvn($path)) {
            return $this->commitChangesSvn($path, $message);
        }

        return ['error' => 'Path is not version controlled by Git or SVN'];
    }
}
