<?php

return [

    'api' => [
        'type' => 'prefix',
        'path' => '/api(/)',

        'resolver' => [
            [
                'type' => 'pattern',
                'path' => '<controller>(/<action>)',

                'defaults' => [
                    'controller' => 'upload',
                    'action'     => 'default',
                ]
            ]
        ],

        'defaults' => [
            'runner'   => 'api',
            'notFound' => 'default'
        ]
    ],

    // default
    'error' => [
        'type' => 'pattern',
        'path' => '/.*',

        'defaults' => [
            'notFound' => 'default'
        ]
    ]

];