<?php

/*
 * This file is part of the 2amigos/mail-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use App\Application\Document\DocumentStorePostAction;
use App\Application\Document\DocumentStatusGetAction;
use App\Application\Document\DocumentSingleGetAction;
use App\Application\Document\DocumentListGetAction;
use App\Application\Document\DocumentDeleteAction;

$app->group('/documents', function () {

    $this->post('/store', DocumentStorePostAction::class)
        ->setArguments([
            'scopes' => [
                'document.all',
                'document.store.post'
            ],
            'input_filter' => 'document_store',
        ]);

    $this->get('/status/{uuid}[/{tag}]', DocumentStatusGetAction::class)
        ->setArguments([
            'scopes' => [
                'document.all',
                'document.status.get'
            ]
        ]);

    $this->get('/list[/{tag}]', DocumentListGetAction::class)
        ->setArguments([
            'scopes' => [
                'document.all',
                'document.get.list'
            ]
        ]);

    $this->get('/{uuid}[/{tag}]', DocumentSingleGetAction::class)
        ->setArguments([
            'scopes' => [
                'document.all',
                'document.single.get'
            ]
        ]);

    $this->delete('/{uuid}', DocumentDeleteAction::class)
        ->setArguments([
            'scopes' => [
                'document.all',
                'document.delete'
            ]
        ]);

});