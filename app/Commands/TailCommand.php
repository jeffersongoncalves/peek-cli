<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use LaravelZero\Framework\Commands\Command;

class TailCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'tail {connection : Saved connection profile name}
        {path : Remote file (e.g. /var/log/nginx/error.log)}
        {--lines=100 : Number of lines}';

    protected $description = 'Print the last lines of a remote file';

    public function handle(): int
    {
        $this->output->write($this->remote([
            'tail', '-n', (string) max(1, (int) $this->option('lines')), '--', $this->safePath((string) $this->argument('path')),
        ]));

        return self::SUCCESS;
    }
}
