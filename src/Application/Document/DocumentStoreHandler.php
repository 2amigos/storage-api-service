<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Application\Document\Exception\StoreDocumentException;
use App\Infrastructure\Db\Document\DocumentRepository;
use App\Infrastructure\Enqueue\Document\FsDocumentProducer;
use App\Infrastructure\Flysystem\StorageFactory;
use Exception;
use League\Flysystem\Filesystem;
use Slim\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DocumentStoreHandler
{
    /**
     * @var StorageFactory
     */
    private $storageAdapterFactory;
    /**
     * @var DocumentRepository
     */
    private $documentRepository;
    /**
     * @var FsDocumentProducer
     */
    private $fsDocumentProducer;

    /**
     * DocumentStoreHandler constructor.
     * @param StorageFactory $storageAdapterFactory
     * @param DocumentRepository $documentRepository
     * @param FsDocumentProducer $fsDocumentProducer
     */
    public function __construct(
        StorageFactory $storageAdapterFactory,
        DocumentRepository $documentRepository,
        FsDocumentProducer $fsDocumentProducer
    ) {
        $this->storageAdapterFactory = $storageAdapterFactory;
        $this->documentRepository = $documentRepository;
        $this->fsDocumentProducer = $fsDocumentProducer;
    }

    /**
     * @param DocumentStoreCommand $command
     * @throws StoreDocumentException
     * @throws \Interop\Queue\Exception
     * @return array
     */
    public function handle(DocumentStoreCommand $command)
    {
        $config = $command->getConfiguration();
        $document = $config['document'];
        $filesystem = $this->storageAdapterFactory->fromAdapterName($config['params']['stores']);

        $data = [
            'success' => true,
            'status' => $command->getStatus(),
            'uuid' => $command->getUuid()
        ];

        $data['success'] = !$command->isAsync()
            ? $this->save($document, $filesystem, $command)
            : $this->sendToQueue($command, $document);

        $data['status'] = $command->getStatus();
        return $data;
    }

    /**
     * @param UploadedFile $document
     * @param Filesystem $filesystem
     * @param DocumentStoreCommand $command
     * @throws Exception
     * @throws StoreDocumentException
     * @return bool
     */
    private function save(UploadedFile $document, Filesystem $filesystem, DocumentStoreCommand $command): bool
    {
        $documentId = $command->getDocumentId();

        try {
            $this->updateStatus($command, $documentId, DocumentStatusInterface::PROCESSING);
            $this->documentRepository->update($documentId, ['path' => $command->getPath()]);

            $filesystem->write($command->getName(), file_get_contents($document->file));
            return $this->updateStatus($command, $documentId, DocumentStatusInterface::UPLOADED);
        } catch (Exception $e) {
            $this->updateStatus($command, $command->getDocumentId(), DocumentStatusInterface::FAILED);
            throw new StoreDocumentException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param DocumentStoreCommand $command
     * @param UploadedFile $document
     * @throws StoreDocumentException
     * @throws \Interop\Queue\Exception
     * @throws Exception
     * @return bool
     */
    private function sendToQueue(DocumentStoreCommand $command, UploadedFile $document)
    {
        $data = $this->parseDocument($command, $document);

        try {
            $this->fsDocumentProducer->send($data, null);
            $this->updateStatus($command, $data['id'], DocumentStatusInterface::PROCESSING);
        } catch (Exception $e) {
            $this->updateStatus($command, $command->getDocumentId(), DocumentStatusInterface::FAILED);
            throw new StoreDocumentException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return true;
    }

    /**
     * @param DocumentStoreCommand $command
     * @param UploadedFile $document
     * @throws Exception
     * @return array
     */
    private function parseDocument(DocumentStoreCommand $command, UploadedFile $document): array
    {
        return [
            'id' => $command->getDocumentId(),
            'uuid' => $command->getUuid(),
            'name' => $command->getName(),
            'tag' => $command->getTag(),
            'status' => $command->getStatus(),
            'storage' => $command->getStorage(),
            'path' => $command->getPath(),
            'document' => $document
        ];
    }

    /**
     * @param DocumentStoreCommand $command
     * @param int $documentId
     * @param int $status
     * @throws \Doctrine\DBAL\DBALException
     * @return bool
     */
    private function updateStatus(DocumentStoreCommand $command, int $documentId, int $status): bool
    {
        $result = $this->documentRepository->updateStatus($documentId, $status);
        $command->setStatus($status);

        return (bool)$result;
    }
}
