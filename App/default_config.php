<?php
/**
 * Copyright (c) 2019. karlharris.org
 */

return [
    'registeredControllers' => [
        '404'
    ],
    'debug' => \false,
    'loggerEmail' => '',
    'show_errors' => \true,
    'timezone' => 'Europe/Berlin',
    'theme' => 'default',
    'inheritTheme' => [
        'test'
    ],
    'defaultJs' => [
        'internal' => [
            [
                'file' => 'index.js',
                'sort' => 0
            ],[
                'file' => 'test.js',
                'sort' => -3,
                'override' => \false
            ]
        ],
        'external' => []
    ],
    'defaultLess' => [
        'internal' => [
            [
                'file' => 'all.less',
                'sort' => 0
            ]
        ],
        'external' => []
    ],
    'cache' => [
        'less' => \false
    ]
];