<?php

return [
    'mode' => 'local', //local|dev|prod
    'db' => [
        'local' => [
            'host' => 'mysql',
            'port' => '3306',
            'dbname' => 'websocketdb',
            'username' => 'root',
            'password' => 'secret',
        ],
        'dev' => [
            'host' => 'mysql',
            'port' => '3306',
            'dbname' => 'websocketdb',
            'username' => 'root',
            'password' => 'secret',
        ],
        'prod' => [
            'host' => 'mysql',
            'port' => '3306',
            'dbname' => 'websocketdb',
            'username' => 'root',
            'password' => 'secret',
        ],
    ],
    'client_services' => [
        'cos' => [
            'authentication' => [
                'authApiUri' => 'cos2-test.dcbi.aero/rest/api/v1/auth/login',
                'options' => [
                    'username' => '',
                    'password' => '',
                ],
            ],
            'socket' => [
                'address' => 'wss://cos.dcbi.aero/rest/api/v1/notifications/subscribe/cos',
            ],
        ],
        'masterdata' => [
            'authentication' => [
                'authApiUri' => 'cos2-test.dcbi.aero/rest/api/v1/auth/login',
                'options' => [
                    'username' => '',
                    'password' => '',
                ],
            ],
        ]
    ],
];