<?php

it('greets the given name', function () {
    $this->artisan('example', ['name' => 'world'])->assertExitCode(0);
});
