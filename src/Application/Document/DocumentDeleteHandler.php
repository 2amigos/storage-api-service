<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Application\Document\Exception\DocumentDeleteException;
use App\Application\Document\Exception\DocumentNotFoundException;
use App\Infrastructure\Db\Document\DocumentQuery;
use App\Infrastructure\Db\Document\DocumentRepository;
use App\Infrastructure\Flysystem\StorageFactory;
use Exception;

class DocumentDeleteHandler
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
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * DocumentDeleteHandler constructor.
     * @param StorageFactory $storageFactory
     * @param DocumentQuery $documentQuery
     * @param DocumentRepository $documentRepository
     */
    public function __construct(
        StorageFactory $storageFactory,
        DocumentQuery $documentQuery,
        DocumentRepository $documentRepository
    ) {
        $this->storageFactory = $storageFactory;
        $this->documentQuery = $documentQuery;
        $this->documentRepository = $documentRepository;
    }

    /**
     * @param DocumentDeleteCommand $command
     * @throws DocumentNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \League\Flysystem\FileNotFoundException
     * @return array
     */
    public function handle(DocumentDeleteCommand $command)
    {
        $config = $command->getConfiguration();
        $document = $this->documentQuery->findOneByUuidAndTag($config['uuid']);

        if (empty($document)) {
            throw new DocumentNotFoundException();
        }

        $filesystem = $this->storageFactory->fromAdapterName($document['storage']);

        $this->documentRepository->updateStatus((int)$document['id'], DocumentStatusInterface::DELETED);
        if (!$filesystem->delete($document['name'])) {
            throw new DocumentNotFoundException('Could not delete the document.');
        }

        return ['success' => true];
    }
}
