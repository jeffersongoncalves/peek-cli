<?php

use App\DTOs\Connection;
use App\Services\SshService;
use Symfony\Component\Process\Process;

it('builds a non-interactive ssh command with port, key and user', function () {
    $connection = new Connection(name: 'prod', host: 'example.com', port: 2222, username: 'deploy', privateKey: '/k/id');

    expect((new SshService)->command($connection, ['uptime']))->toBe([
        'ssh',
        '-o', 'BatchMode=yes',
        '-o', 'StrictHostKeyChecking=accept-new',
        '-o', 'ConnectTimeout=10',
        '-p', '2222',
        '-i', '/k/id',
        '--',
        'deploy@example.com',
        "'env' 'LC_ALL=C' 'uptime'",
    ]);
});

it('leaves port, key and user to ~/.ssh/config when unset', function () {
    $command = (new SshService)->command(new Connection(name: 'prod', host: 'prod-alias'), ['uptime']);

    expect($command)->not->toContain('-p')
        ->and($command)->not->toContain('-i')
        ->and($command[count($command) - 2])->toBe('prod-alias');
});

it('quotes every argument so shell metacharacters stay literal', function () {
    $remote = (new SshService)->remoteCommand(['grep', '-e', "x'; rm -rf / #", '--', '$(reboot) | > /etc/passwd']);

    expect($remote)->toBe("'env' 'LC_ALL=C' 'grep' '-e' 'x'\\''; rm -rf / #' '--' '\$(reboot) | > /etc/passwd'");
});

it('round-trips arbitrary arguments through a real POSIX shell', function () {
    $sh = trim((string) shell_exec(PHP_OS_FAMILY === 'Windows' ? 'where sh' : 'command -v sh'));

    if ($sh === '') {
        $this->markTestSkipped('No POSIX sh available.');
    }

    $args = ["it's", '$(id)', '`id`', 'a;b|c>d', '%y\t%s\n', '*', '"quoted"', '!bang'];
    $script = implode(' ', array_map(SshService::quote(...), ['printf', '%s\n', ...$args]));

    $process = new Process([strtok($sh, "\r\n"), '-c', $script]);
    $process->mustRun();

    expect(explode("\n", rtrim($process->getOutput(), "\n")))->toBe($args);
});
