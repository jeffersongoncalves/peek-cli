<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use LaravelZero\Framework\Commands\Command;

class SystemctlCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'systemctl {connection : Saved connection profile name}
        {unit : Service unit (e.g. nginx, php8.4-fpm)}
        {--lines=20 : Journal lines to include}';

    protected $description = 'Show `systemctl status` for a remote service (status only, never start/stop)';

    public function handle(): int
    {
        // systemctl status exits 3 for an inactive unit and 4 for an unknown one; both are answers.
        $this->output->write($this->remote([
            'systemctl', 'status', '--no-pager', '--full', '-n', (string) max(0, (int) $this->option('lines')),
            '--', (string) $this->argument('unit'),
        ], [0, 3, 4]));

        return self::SUCCESS;
    }
}
