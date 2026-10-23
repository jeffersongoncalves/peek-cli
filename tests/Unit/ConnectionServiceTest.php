<?php

use App\DTOs\Connection;
use App\Services\ConnectionService;

beforeEach(function () {
    $this->originalHome = getenv('HOME');
    $this->tempHome = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('peek-cli-test-');
    mkdir($this->tempHome);
    putenv('HOME='.$this->tempHome);
});

afterEach(function () {
    putenv($this->originalHome === false ? 'HOME' : "HOME={$this->originalHome}");
});

it('saves and reloads a connection profile', function () {
    (new ConnectionService)->save(new Connection(
        name: 'prod',
        host: 'example.com',
        port: 2222,
        username: 'deploy',
        privateKey: '/home/me/.ssh/id_ed25519',
    ));

    $reloaded = (new ConnectionService)->getOrFail('prod');

    expect($reloaded->host)->toBe('example.com')
        ->and($reloaded->port)->toBe(2222)
        ->and($reloaded->username)->toBe('deploy')
        ->and($reloaded->privateKey)->toBe('/home/me/.ssh/id_ed25519');
});

it('saves a bare ssh-config alias without user, port or key', function () {
    (new ConnectionService)->save(new Connection(name: 'prod', host: 'prod-alias'));

    $reloaded = (new ConnectionService)->getOrFail('prod');

    expect($reloaded->port)->toBeNull()
        ->and($reloaded->username)->toBeNull()
        ->and($reloaded->target())->toBe('prod-alias');
});

it('removes a connection profile', function () {
    $connections = new ConnectionService;
    $connections->save(new Connection(name: 'prod', host: 'example.com'));

    expect($connections->forget('prod'))->toBeTrue()
        ->and($connections->forget('prod'))->toBeFalse()
        ->and($connections->get('prod'))->toBeNull();
});
