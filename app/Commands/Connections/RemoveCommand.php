<?php

namespace App\Commands\Connections;

use App\Services\ConnectionService;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;

class RemoveCommand extends Command
{
    protected $signature = 'connections:remove
        {name : Profile name}
        {--force : Skip confirmation}';

    protected $description = 'Remove a saved SSH server profile';

    public function handle(ConnectionService $connections): int
    {
        $name = (string) $this->argument('name');

        if (! $this->option('force') && ! confirm("Remove connection \"{$name}\"?", default: false)) {
            $this->components->warn('Aborted.');

            return self::SUCCESS;
        }

        if (! $connections->forget($name)) {
            $this->components->error("Connection \"{$name}\" not found.");

            return self::FAILURE;
        }

        $this->components->info("Connection \"{$name}\" removed.");

        return self::SUCCESS;
    }
}
