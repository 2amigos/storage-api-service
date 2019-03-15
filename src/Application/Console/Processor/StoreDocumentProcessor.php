<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Console\Processor;

use App\Application\Document\DocumentStatusInterface;
use App\Infrastructure\Db\Document\DocumentRepository;
use App\Infrastructure\Flysystem\StorageFactory;
use Exception;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Psr\Log\LoggerInterface;

class StoreDocumentProcessor implements Processor
{
    /**
     * @var StorageFactory
     */
    private $storageFactory;
    /**
     * @var DocumentRepository
     */
    private $documentRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * StoreDocumentProcessor constructor.
     * @param StorageFactory $storageFactory
     * @param DocumentRepository $documentRepository
     * @param LoggerInterface $logger
     */
    public function __construct(StorageFactory $storageFactory, DocumentRepository $documentRepository, LoggerInterface $logger)
    {
        $this->storageFactory = $storageFactory;
        $this->documentRepository = $documentRepository;
        $this->logger = $logger;
    }

    /**
     * @param Message $message
     * @param Context $context
     * @throws \Doctrine\DBAL\DBALException
     * @return object|string
     */
    public function process(Message $message, Context $context)
    {
        $this->logger->info('Document to upload received');
        $stored = true;

        $data = json_decode($message->getBody(), true);

        try {
            $filesystem = $this->storageFactory->fromAdapterName($data['storage']);

            $this->logger->info(
                printf(
                    'Storing document with name "%s" and uuid "%s"',
                    $data['name'],
                    $data['uuid']
                )
            );

            if (!(bool)$filesystem->write($data['name'], file_get_contents($data['document']['file']))) {
                $this->logger->warning('Unable to store document with data: ' . $message->getBody());
                $this->documentRepository->updateStatus((int)$data['id'], DocumentStatusInterface::FAILED);
                $stored = false;
            } else {
                $this->documentRepository->updateStatus((int)$data['id'], DocumentStatusInterface::UPLOADED);
                $path = $filesystem->getAdapter()->getPathPrefix();
                $this->documentRepository->update((int)$data['id'], ['path' => $path]);
                $this->logger->info('Document successfully stored.');

            }
        } catch (Exception $exception) {
            $this->documentRepository->updateStatus((int)$data['id'], DocumentStatusInterface::FAILED);
            $this->logger->error($exception->getMessage());
            $stored = false;
        }

        return $stored ? self::ACK : self::REJECT;
    }
}
