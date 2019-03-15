<?php

// load our environment files - used to store credentials & configuration
(new \Symfony\Component\Dotenv\Dotenv())->load('.env');

return
    [
        'paths' => [
            'migrations' => 'config/db/migrations',
            'seeds' => 'config/db/seeds'
        ],
        'environments' =>
            [
                'default_database' => 'development',
                'default_migration_table' => 'phinxlog',
                'development'      =>
                    [
                        'adapter' => 'mysql',
                        'host' => getenv('DATABASE_HOST'),
                        'name' => getenv('DATABASE_NAME'),
                        'user' => getenv('DATABASE_USERNAME'),
                        'pass' => getenv('DATABASE_PASSWORD'),
                        'port' => 3306,
                        'charset' => 'utf8',
                        'collation' => 'utf8_unicode_ci',
                    ],
                'testing' =>
                    [
                        'adapter' => 'mysql',
                        'host' => getenv('DATABASE_HOST'),
                        'name' => getenv('DATABASE_NAME'),
                        'user' => getenv('DATABASE_USERNAME'),
                        'pass' => getenv('DATABASE_PASSWORD'),
                        'port' => 3306,
                        'charset' => 'utf8',
                        'collation' => 'utf8_unicode_ci',
                    ],
                'production' =>
                    [
                        'adapter' => 'mysql',
                        'host' => getenv('DATABASE_HOST'),
                        'name' => getenv('DATABASE_NAME'),
                        'user' => getenv('DATABASE_USERNAME'),
                        'pass' => getenv('DATABASE_PASSWORD'),
                        'port' => 3306,
                        'charset' => 'utf8',
                        'collation' => 'utf8_unicode_ci',
                    ],
            ],
    ];