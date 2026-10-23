<?php

namespace App\Services;

use App\DTOs\Connection;
use App\Exceptions\RemoteCommandException;
use Symfony\Component\Process\Process;

class SshService
{
    private const TIMEOUT_SECONDS = 60;

    /**
     * Runs a fixed argv on the remote host and returns its stdout. Every
     * argument is single-quoted for the remote POSIX shell, so nothing the
     * caller passes (paths, patterns) can turn into `;`, `|`, `$()` or `>`.
     *
     * @param  list<string>  $argv
     * @param  list<int>  $okExitCodes  exit codes that still mean "here is the output" (grep: 1 = no match)
     */
    public function run(Connection $connection, array $argv, array $okExitCodes = [0]): string
    {
        $process = new Process($this->command($connection, $argv));
        $process->setTimeout(self::TIMEOUT_SECONDS);
        $process->run();

        $exitCode = (int) $process->getExitCode();

        if ($exitCode === 255) {
            throw RemoteCommandException::ssh($connection->name, $process->getErrorOutput());
        }

        if (! in_array($exitCode, $okExitCodes, true)) {
            throw RemoteCommandException::failed($connection->name, $argv[0], $exitCode, $process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * @param  list<string>  $argv
     * @return list<string>
     */
    public function command(Connection $connection, array $argv): array
    {
        $command = [
            'ssh',
            '-o', 'BatchMode=yes',
            '-o', 'StrictHostKeyChecking=accept-new',
            '-o', 'ConnectTimeout=10',
        ];

        if ($connection->port !== null) {
            $command[] = '-p';
            $command[] = (string) $connection->port;
        }

        if ($connection->privateKey !== null) {
            $command[] = '-i';
            $command[] = $connection->privateKey;
        }

        $command[] = '--';
        $command[] = $connection->target();
        $command[] = $this->remoteCommand($argv);

        return $command;
    }

    /**
     * @param  list<string>  $argv
     */
    public function remoteCommand(array $argv): string
    {
        // LC_ALL=C keeps ps/df/free headers and number formats parseable.
        return implode(' ', array_map(self::quote(...), ['env', 'LC_ALL=C', ...$argv]));
    }

    public static function quote(string $argument): string
    {
        return "'".str_replace("'", "'\\''", $argument)."'";
    }
}
