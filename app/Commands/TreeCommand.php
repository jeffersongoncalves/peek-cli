<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use App\Support\OutputParser;
use LaravelZero\Framework\Commands\Command;

class TreeCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'tree {connection : Saved connection profile name}
        {path=. : Remote directory}
        {--depth=2 : How many levels deep to go}
        {--all : Also descend into .git, node_modules and vendor}
        {--format=text : text|json|csv}';

    protected $description = 'Show a remote directory tree';

    private const PRUNED = ['.git', 'node_modules', 'vendor'];

    public function handle(): int
    {
        $argv = [
            'find', $this->safePath((string) $this->argument('path')),
            '-mindepth', '1', '-maxdepth', (string) max(1, (int) $this->option('depth')),
        ];

        if (! $this->option('all')) {
            $argv[] = '(';

            foreach (self::PRUNED as $i => $name) {
                array_push($argv, ...($i === 0 ? ['-name', $name] : ['-o', '-name', $name]));
            }

            array_push($argv, ')', '-prune', '-o');
        }

        array_push($argv, '-printf', '%d\t%y\t%P\n');

        $rows = OutputParser::tree($this->remote($argv));
        $format = (string) $this->option('format');

        if ($format !== 'text') {
            $this->renderRows($rows, $format);

            return self::SUCCESS;
        }

        foreach ($rows as $row) {
            $this->line(str_repeat('  ', $row['depth'] - 1).basename($row['path']).($row['type'] === 'dir' ? '/' : ''));
        }

        return self::SUCCESS;
    }
}
