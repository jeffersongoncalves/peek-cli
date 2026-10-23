<?php

namespace App\Commands\Connections;

use App\DTOs\Connection;
use App\Services\ConnectionService;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\text;

class AddCommand extends Command
{
    protected $signature = 'connections:add
        {name? : Profile name (e.g. "prod")}
        {--host= : SSH host, or a Host alias from ~/.ssh/config}
        {--port= : SSH port (leave empty to use ~/.ssh/config or 22)}
        {--username= : SSH username (leave empty to use ~/.ssh/config)}
        {--private-key= : Path to the SSH private key (leave empty to use ssh-agent/default identity)}';

    protected $description = 'Save a named SSH server profile';

    public function handle(ConnectionService $connections): int
    {
        $name = $this->argument('name') ?: text(label: 'Profile name', placeholder: 'prod', required: true);
        $host = $this->option('host') ?: text(label: 'SSH host', hint: 'Hostname, IP, or a Host alias from ~/.ssh/config', required: true);

        if (str_starts_with($host, '-') || preg_match('/\s/', $host) === 1) {
            $this->components->error("Invalid host \"{$host}\".");

            return self::FAILURE;
        }

        $port = $this->option('port') ?? text(label: 'SSH port', hint: 'Leave empty to use ~/.ssh/config or 22');
        $username = $this->option('username') ?? text(label: 'SSH username', hint: 'Leave empty to use ~/.ssh/config');
        $privateKey = $this->option('private-key') ?? text(label: 'SSH private key path', hint: 'Leave empty to use ssh-agent/default identity');

        $connections->save(new Connection(
            name: $name,
            host: $host,
            port: $port ? (int) $port : null,
            username: $username ?: null,
            privateKey: $privateKey ?: null,
        ));

        $this->components->info("Connection \"{$name}\" saved to {$connections->path()}");

        return self::SUCCESS;
    }
}
