<?php

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Zend\Validator\Callback;

if (file_exists(__DIR__ . '/settings-env.php')) {
    $override = require __DIR__ . '/settings-env.php';
} elseif (file_exists(__DIR__ . '/settings-local.php')) {
    $override = require __DIR__ . '/settings-local.php';
} elseif (file_exists(__DIR__ . '/env/' . getenv('APP_ENV') . '/settings.php')) {
    $override = require __DIR__ . '/env/' . getenv('APP_ENV') . '/settings.php';
} else {
    $override = [];
}

if(!defined('APP_PROJECT_ROOT')){
    define('APP_PROJECT_ROOT', __DIR__ . '/../../');
}
if(!defined('STORAGE')){
    define('STORAGE', ['local']);
}

return array_merge(
    [
        'db' => [
            'driver' => getenv('DATABASE_DRIVER'),
            'host' => getenv('DATABASE_HOST'),
            'dbname' => getenv('DATABASE_NAME'),
            'user' => getenv('DATABASE_USERNAME'),
            'password' => getenv('DATABASE_PASSWORD')
        ],

        'determineRouteBeforeAppMiddleware' => true,

        'displayErrorDetails' => true,

        'scopes' => [
            'document.all',
            'document.store.post',
            'document.status.get',
            'document.single.get',
            'document.list.get',
            'document.delete'
        ],

        'storage' => [
            'local' => [
                'adapter' => 'storage.local.adapter',
                'args' => [
                    'root' => getenv('DOCUMENT_LOCAL_PATH'),
                    'write_flags' => getenv('DOCUMENT_LOCAL_WRITE_FLAGS'),
                    'link_handling' => getenv('DOCUMENT_LOCAL_LINK_HANDLING'),
                    'permissions' => []
                ]
            ]
        ],

        'queue' => [
            'name' => getenv('ENQUEUE_APP_STORAGE_NAME'),
            'path' => APP_PROJECT_ROOT . 'runtime/queue',
            'pre_fetch_count' => 1, // default value
            'polling_interval' => 100 // default value 100 ms
        ],

        'token' => [
            'lifespan' => 'now +2 hours',
        ],

        'views' => APP_PROJECT_ROOT . 'views',

        'input_filter_specs' => [
            'document_store' => [
                [
                    'name' => 'stores',
                    'required' => true,
                    'filters' => [
                        ['name' => 'StringTrim'],
                    ],
                    'validators' => [
                        [
                            'name' => 'Callback',
                            'options' => [
                                'messages' => [
                                    Callback::INVALID_VALUE => 'Unknown storage',
                                ],
                                'callback' => function (string $value) {
                                    return \in_array($value, STORAGE, false);
                                },
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'name',
                    'required' => false,
                    'filters' => [
                        ['name' => 'StringTrim'],
                    ],
                ],
                [
                    'name' => 'document',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => 'fileUploadFile',
                        ],
                    ],
                ],
                [
                    'name' => 'async',
                    'required' => false,
                    'default' => false
                ],
                [
                    'name' => 'tag',
                    'required' => false,
                    'filters' => [
                        ['name' => 'StringTrim'],
                    ],
                ]
            ],
        ],
    ],
    $override
);
