<?php

use App\Providers\AppServiceProvider;

return [

    'name' => 'Peek CLI',

    'version' => app('git.version'),

    'env' => 'development',

    'providers' => [
        AppServiceProvider::class,
    ],

];
