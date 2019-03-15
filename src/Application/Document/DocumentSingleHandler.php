<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Application\Document\Exception\DocumentNotFoundException;
use App\Application\Document\Exception\DocumentSingleException;
use App\Infrastructure\Db\Document\DocumentQuery;
use App\Infrastructure\Flysystem\StorageFactory;
use Exception;

class DocumentSingleHandler
{
    /**
     * @var StorageFactory
     */
    private $storageFactory;
    /**
     * @var DocumentQuery
     */
    private $documentQuery;

    /**
     * DocumentSingleGetHandler constructor.
     * @param StorageFactory $storageFactory
     * @param DocumentQuery $documentQuery
     */
    public function __construct(StorageFactory $storageFactory, DocumentQuery $documentQuery)
    {
        $this->storageFactory = $storageFactory;
        $this->documentQuery = $documentQuery;
    }

    /**
     * @param DocumentSingleCommand $command
     * @throws DocumentSingleException
     * @return array
     */
    public function handle(DocumentSingleCommand $command)
    {
        try {
            $config = $command->getConfiguration();
            $document = $this->documentQuery->findOneByUuidAndTag($config['uuid'], $config['tag']);

            if (empty($document)) {
                throw new DocumentNotFoundException('Document not found');
            }

            $filesystem = $this->storageFactory->fromAdapterName($document['storage']);

            if (!$filesystem->has($document['name'])) {
                throw new Exception('File not found');
            }

            return [
                'file' => $filesystem->read($document['name']),
                'mime_type' => $filesystem->getMimetype($document['name']),
                'meta' => $filesystem->getMetadata($document['name'])
            ];
        } catch (DocumentNotFoundException $e) {
            throw new $e;
        } catch (Exception $e) {
            throw new DocumentSingleException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}
