<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// something which should probably be served as a static file
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

require __DIR__ . '/../../vendor/autoload.php';

(new \Symfony\Component\Dotenv\Dotenv())->load(__DIR__ . '/../../.env.test');

require __DIR__ . '/../../config/api/bootstrap.php';
