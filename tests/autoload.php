<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to add "symfony/dotenv" as a Composer dependency to load variables from a .env.test file.');
    }
    (new Dotenv())->load(__DIR__.'/../.env.test');
}