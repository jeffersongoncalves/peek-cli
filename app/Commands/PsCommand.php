<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use App\Support\OutputParser;
use LaravelZero\Framework\Commands\Command;

class PsCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'ps {connection : Saved connection profile name}
        {--sort=cpu : cpu|mem}
        {--limit=20 : Number of processes to show}
        {--format=table : table|json|csv}';

    protected $description = 'List the top remote processes by CPU or memory';

    public function handle(): int
    {
        $sort = $this->option('sort') === 'mem' ? '-pmem' : '-pcpu';

        $rows = OutputParser::processes($this->remote(['ps', '-eo', 'pid,user,pcpu,pmem,etime,args', "--sort={$sort}"]));

        $this->renderRows(array_slice($rows, 0, max(1, (int) $this->option('limit'))), (string) $this->option('format'));

        return self::SUCCESS;
    }
}
