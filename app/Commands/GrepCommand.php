<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use App\Support\OutputParser;
use LaravelZero\Framework\Commands\Command;

class GrepCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'grep {connection : Saved connection profile name}
        {pattern : Text to search for (literal unless --regex)}
        {path : Remote file, or directory with --recursive}
        {--regex : Treat the pattern as an extended regex instead of a literal string}
        {--ignore-case : Case-insensitive match}
        {--recursive : Search a directory recursively (skips .git)}
        {--max=200 : Stop after this many matching lines}
        {--format=text : text|json|csv}';

    protected $description = 'Search a remote file or directory for matching lines';

    public function handle(): int
    {
        $max = max(1, (int) $this->option('max'));
        $argv = ['grep', '-H', '-n', '-I', '-m', (string) $max, $this->option('regex') ? '-E' : '-F'];

        if ($this->option('ignore-case')) {
            $argv[] = '-i';
        }

        if ($this->option('recursive')) {
            array_push($argv, '-r', '--exclude-dir=.git');
        }

        array_push($argv, '-e', (string) $this->argument('pattern'), '--', $this->safePath((string) $this->argument('path')));

        // grep exits 1 when nothing matched; that is an answer, not an error.
        $lines = array_slice(preg_split('/\r?\n/', rtrim($this->remote($argv, [0, 1]))) ?: [], 0, $max);
        $lines = array_values(array_filter($lines, static fn (string $line) => $line !== ''));
        $format = (string) $this->option('format');

        if ($format !== 'text') {
            $this->renderRows(OutputParser::matches(implode("\n", $lines)), $format);

            return self::SUCCESS;
        }

        $lines === [] ? $this->line('(no matches)') : $this->line(implode("\n", $lines));

        return self::SUCCESS;
    }
}
