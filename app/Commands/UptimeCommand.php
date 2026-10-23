<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use LaravelZero\Framework\Commands\Command;

class UptimeCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'uptime {connection : Saved connection profile name}';

    protected $description = 'Show remote uptime and load average (also a quick connection check)';

    public function handle(): int
    {
        $this->line(trim($this->remote(['uptime'])));

        return self::SUCCESS;
    }
}
