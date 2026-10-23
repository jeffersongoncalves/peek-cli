<?php

use App\Exceptions\RemoteCommandException;
use App\Services\ConnectionService;

beforeEach(function () {
    $this->originalHome = getenv('HOME');
    $this->tempHome = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('peek-cli-test-');
    mkdir($this->tempHome);
    putenv('HOME='.$this->tempHome);
    $this->app->forgetInstance(ConnectionService::class);
});

afterEach(function () {
    putenv($this->originalHome === false ? 'HOME' : "HOME={$this->originalHome}");
});

it('adds and lists a profile non-interactively', function () {
    $this->artisan('connections:add prod --host=example.com --port=2222 --username=deploy --private-key=')
        ->assertSuccessful();

    $this->artisan('connections:list --format=json')
        ->expectsOutputToContain('"host": "example.com"')
        ->assertSuccessful();
});

it('rejects a host that would be read as an ssh option', function () {
    $this->artisan('connections:add evil --host=-oProxyCommand=id --port= --username= --private-key=')
        ->assertFailed();
});

it('refuses a path that would be read as an option before connecting', function () {
    $this->artisan('connections:add prod --host=example.com --port= --username= --private-key=')->assertSuccessful();

    $this->artisan('ls prod -- -delete');
})->throws(RemoteCommandException::class, 'may not start with "-"');
