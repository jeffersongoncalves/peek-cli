<?php

namespace App\Commands\Connections;

use App\Concerns\RunsRemoteCommands;
use App\Services\ConnectionService;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'connections:list {--format=table : table|json|csv}';

    protected $description = 'List saved SSH server profiles';

    public function handle(ConnectionService $connections): int
    {
        $rows = [];

        foreach ($connections->all() as $connection) {
            $rows[] = [
                'name' => $connection->name,
                'host' => $connection->host,
                'port' => $connection->port ?? '-',
                'username' => $connection->username ?? '-',
                'private_key' => $connection->privateKey ?? '-',
            ];
        }

        $this->renderRows($rows, (string) $this->option('format'));

        return self::SUCCESS;
    }
}
