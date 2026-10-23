<?php

namespace App\Concerns;

use App\Exceptions\RemoteCommandException;
use App\Services\ConnectionService;
use App\Services\SshService;

trait RunsRemoteCommands
{
    /**
     * Runs $argv on the host saved as the command's `connection` argument.
     *
     * @param  list<string>  $argv
     * @param  list<int>  $okExitCodes
     */
    protected function remote(array $argv, array $okExitCodes = [0]): string
    {
        $connection = app(ConnectionService::class)->getOrFail((string) $this->argument('connection'));

        return app(SshService::class)->run($connection, $argv, $okExitCodes);
    }

    /**
     * Quoting already stops shell injection; this stops option injection,
     * e.g. a "path" of `-delete` handed to find.
     */
    protected function safePath(string $path): string
    {
        if (str_starts_with($path, '-')) {
            throw RemoteCommandException::unsafePath($path);
        }

        return $path;
    }

    /**
     * Renders rows as table (human), json, or csv (for LLM/pipe consumption).
     *
     * @param  list<array<string, scalar|null>>  $rows
     */
    protected function renderRows(array $rows, string $format): void
    {
        match ($format) {
            'json' => $this->line(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]'),
            'csv' => $this->renderCsv($rows),
            default => $rows === [] ? $this->line('(no rows)') : $this->table(array_keys($rows[0]), $rows),
        };
    }

    /**
     * @param  list<array<string, scalar|null>>  $rows
     */
    private function renderCsv(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $stream = fopen('php://output', 'w');

        fputcsv($stream, array_keys($rows[0]), escape: '');

        foreach ($rows as $row) {
            fputcsv($stream, $row, escape: '');
        }

        fclose($stream);
    }
}
