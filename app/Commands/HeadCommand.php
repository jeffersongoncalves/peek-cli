<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use LaravelZero\Framework\Commands\Command;

class HeadCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'head {connection : Saved connection profile name}
        {path : Remote file}
        {--lines=100 : Number of lines}';

    protected $description = 'Print the first lines of a remote file';

    public function handle(): int
    {
        $this->output->write($this->remote([
            'head', '-n', (string) max(1, (int) $this->option('lines')), '--', $this->safePath((string) $this->argument('path')),
        ]));

        return self::SUCCESS;
    }
}
