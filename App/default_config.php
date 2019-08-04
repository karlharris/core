<?php
/**
 * Copyright (c) 2018 - 2019. karlharris.org
 */

return [
    'registeredControllers' => [
        '404',
        'widgets',
        'lvl1' => [
            'lvl2'
        ]
    ],
    'registeredWidgets' => [],
    'debug' => [
        'theme' => [
            'template' => \false,
            'resource' => \false
        ],
        'pathInfo' => \false,
        'plugin' => \false
    ],
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
                'file' => 'widgets.js',
                'sort' => 0
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
        'js' => \false,
        'html' => \false
    ],
    'databaseLock' => \true,
    'vue' => \true
];