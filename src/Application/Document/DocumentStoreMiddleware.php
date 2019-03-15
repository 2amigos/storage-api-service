<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Infrastructure\Db\Document\DocumentRepository;
use League\Tactician\Middleware;
use Ramsey\Uuid\Uuid;

class DocumentStoreMiddleware implements Middleware
{
    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * DocumentStoreMiddleware constructor.
     * @param DocumentRepository $documentRepository
     */
    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    /**
     * @param object $command
     * @param callable $next
     * @throws \Exception
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        if ($command instanceof DocumentStoreCommand) {
            $uuid4 = Uuid::uuid4();
            $command->generateName();

            $documentId = $this->documentRepository->create([
                'uuid' => $uuid4->getBytes(),
                'status' => DocumentStatusInterface::PENDING,
                'name' => $command->getName(),
                'tag' => $command->getTag(),
                'storage' => $command->getStorage()
            ]);

            $command->setStatus(DocumentStatusInterface::PENDING);
            $command->setUuid($uuid4->toString());
            $command->setDocumentId($documentId);
        }

        return $next($command);
    }
}
