<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

return [
    'registeredControllers' => [
        '404',
        'lvl1' => [
            'lvl2'
        ]
    ],
    'debug' => \false,
    'loggerEmail' => '',
    'showErrors' => \true,
    'timezone' => 'Europe/Berlin',
    'theme' => 'default',
    'inheritTheme' => [
        'testTheme'
    ],
    'js' => [
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
    'less' => [
        'internal' => [
            [
                'file' => 'all.less',
                'sort' => 0
            ]
        ],
        'external' => []
    ],
    'cache' => [
        'less' => \false,
        'js' => \false
    ],
    'databaseLock' => \false
];