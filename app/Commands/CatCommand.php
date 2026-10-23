<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use LaravelZero\Framework\Commands\Command;

class CatCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'cat {connection : Saved connection profile name}
        {path : Remote file}
        {--max-bytes=100000 : Stop after this many bytes (protects against dumping a huge file)}';

    protected $description = 'Print a remote file, capped at --max-bytes';

    public function handle(): int
    {
        $max = max(1, (int) $this->option('max-bytes'));

        // One byte more than the cap tells us whether the file was cut.
        $output = $this->remote(['head', '-c', (string) ($max + 1), '--', $this->safePath((string) $this->argument('path'))]);

        $this->output->write(substr($output, 0, $max));

        if (strlen($output) > $max) {
            $this->newLine();
            $this->components->warn("Truncated at {$max} bytes. Use --max-bytes, head or tail to see more.");
        }

        return self::SUCCESS;
    }
}
