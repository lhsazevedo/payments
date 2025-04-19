<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    // TODO: Disable HTTP server
    'server.servers.0.port' => 9502,
    'amqp' => [
        'default' => [
            'host' => env('AMQP_HOST', 'localhost'),
            'port' => intval(env('AMQP_PORT', 5672)),
            'user' => env('AMQP_USER', 'guest'),
            'password' => env('AMQP_PASSWORD', 'guest'),
            'vhost' => '/',
            'concurrent' => [
                'limit' => 1,
            ],
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 10,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
            ],
            'params' => [
                'insist' => false,
                'login_method' => 'AMQPLAIN',
                'login_response' => null,
                'locale' => 'en_US',
                'connection_timeout' => 3.0,
                'read_write_timeout' => 6.0,
                'context' => null,
                'keepalive' => false,
                'heartbeat' => 3,
                'close_on_destruct' => true,
            ],
        ],
    ],
];
