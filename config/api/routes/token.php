<?php

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use App\Application\Token\TokenPostAction;

$app->post('/token', TokenPostAction::class);

/* This is just for debugging, not usefull in real life. */
$app->get('/dump', function ($request, $response, $arguments) {
    print_r($this->token);
});

$app->post('/dump', function ($request, $response, $arguments) {
    print_r($this->token);
});

/* This is just for debugging, not usefull in real life. */
$app->get('/info', function ($request, $response, $arguments) {
    phpinfo();
});
