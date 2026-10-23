<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use App\Support\OutputParser;
use LaravelZero\Framework\Commands\Command;

class DfCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'df {connection : Saved connection profile name}
        {--all : Include tmpfs, devtmpfs, squashfs and overlay mounts}
        {--format=table : table|json|csv}';

    protected $description = 'Show remote disk usage';

    public function handle(): int
    {
        $argv = ['df', '-hP'];

        if (! $this->option('all')) {
            array_push($argv, '-x', 'tmpfs', '-x', 'devtmpfs', '-x', 'squashfs', '-x', 'overlay');
        }

        $this->renderRows(OutputParser::disks($this->remote($argv)), (string) $this->option('format'));

        return self::SUCCESS;
    }
}
