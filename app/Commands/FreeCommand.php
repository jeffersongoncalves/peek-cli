<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use App\Support\OutputParser;
use LaravelZero\Framework\Commands\Command;

class FreeCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'free {connection : Saved connection profile name}
        {--format=table : table|json|csv}';

    protected $description = 'Show remote memory and swap usage';

    public function handle(): int
    {
        $this->renderRows(OutputParser::memory($this->remote(['free', '-h'])), (string) $this->option('format'));

        return self::SUCCESS;
    }
}
