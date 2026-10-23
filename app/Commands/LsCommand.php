<?php

namespace App\Commands;

use App\Concerns\RunsRemoteCommands;
use App\Support\OutputParser;
use LaravelZero\Framework\Commands\Command;

class LsCommand extends Command
{
    use RunsRemoteCommands;

    protected $signature = 'ls {connection : Saved connection profile name}
        {path=. : Remote directory (relative paths start at the SSH user\'s home)}
        {--format=table : table|json|csv}';

    protected $description = 'List a remote directory (hidden files included)';

    public function handle(): int
    {
        $output = $this->remote([
            'find', $this->safePath((string) $this->argument('path')),
            '-mindepth', '1', '-maxdepth', '1',
            '-printf', '%y\t%s\t%TY-%Tm-%Td %TH:%TM\t%u\t%m\t%f\n',
        ]);

        $this->renderRows(OutputParser::listing($output), (string) $this->option('format'));

        return self::SUCCESS;
    }
}
