<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class ExampleCommand extends Command
{
    /**
     * Keep commands non-interactive: every input an argument or flag, so a
     * script or an AI agent can drive them without a TTY. No ->ask()/->confirm().
     *
     * @var string
     */
    protected $signature = 'example {name : who to greet}';

    protected $description = 'Replace me with the first real command';

    public function handle(): int
    {
        $this->components->info('Hello '.$this->argument('name'));

        return self::SUCCESS;
    }
}
