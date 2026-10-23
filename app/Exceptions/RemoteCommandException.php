<?php

namespace App\Exceptions;

use RuntimeException;

class RemoteCommandException extends RuntimeException
{
    public static function ssh(string $connectionName, string $errorOutput): self
    {
        $reason = trim($errorOutput) !== '' ? trim($errorOutput) : 'ssh exited with code 255';

        return new self("SSH connection \"{$connectionName}\" failed: {$reason}");
    }

    public static function failed(string $connectionName, string $program, int $exitCode, string $errorOutput): self
    {
        $reason = trim($errorOutput) !== '' ? trim($errorOutput) : 'no error output';

        return new self("`{$program}` on \"{$connectionName}\" exited with code {$exitCode}: {$reason}");
    }

    public static function unsafePath(string $path): self
    {
        return new self("Refusing path \"{$path}\": paths may not start with \"-\" (use ./{$path} if you really mean it).");
    }
}
