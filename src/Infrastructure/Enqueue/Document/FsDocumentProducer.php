<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Enqueue\Document;

use App\Infrastructure\Enqueue\AbstractProducer;
use Enqueue\Client\Config;

final class FsDocumentProducer extends AbstractProducer
{
    /**
     * @param array $document
     * @param int|null $delay
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    public function send(array $document, ?int $delay): void
    {
        $document = $this->resolver->resolve($document);
        $context = $this->getContext();
        $queueDocument = $context->createMessage(
            json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            [
                Config::PROCESSOR => 'enqueue.storage.processor'
            ]
        );
        $context->createProducer()->send($this->getQueue(), $queueDocument);
    }

    /**
     * @inheritdoc
     */
    protected function configureOptions(): void
    {
        $this->resolver
            ->setDefined(['id', 'uuid', 'name', 'tag', 'status', 'storage', 'path', 'document'])
            ->setRequired(['id', 'uuid', 'name', 'status', 'storage', 'path', 'document'])
            ->setAllowedTypes('id', ['integer'])
            ->setAllowedTypes('uuid', ['string'])
            ->setAllowedTypes('name', ['string'])
            ->setAllowedTypes('tag', ['null', 'string'])
            ->setAllowedTypes('status', ['integer'])
            ->setAllowedTypes('storage', ['string'])
            ->setAllowedTypes('path', ['string'])
            ->setAllowedTypes('document', ['Slim\Http\UploadedFile']);
    }
}
