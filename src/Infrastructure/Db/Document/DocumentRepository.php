<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Db\Document;

use App\Application\Document\DocumentStatusInterface;
use App\Infrastructure\Db\AbstractRepository;
use App\Infrastructure\Db\RepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use http\Exception\InvalidArgumentException;
use Zend\Db\Adapter\Adapter;

class DocumentRepository extends AbstractRepository implements RepositoryInterface
{
    /**
     * @var string
     */
    private $table = 'storage_document';

    /**
     * DocumentRepository constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
    }

    /**
     * @inheritdoc
     * @throws DBALException
     */
    public function create(array $data): int
    {
        $result = $this->connection->insert($this->table, $data);

        return $result ? (int)$this->connection->lastInsertId() : $result;
    }

    /**
     * @param int $id
     * @param array $data
     * @throws DBALException
     * @return int
     */
    public function update(int $id, array $data): int
    {
        return $this->connection->update($this->table, $data, ['id' => $id]);
    }

    /**
     * @param int $documentId
     * @param int $status
     * @throws DBALException
     * @return int
     */
    public function updateStatus(int $documentId, int $status): int
    {
        if (!\in_array($status, DocumentStatusInterface::STATUS_LIST, false)) {
            throw new InvalidArgumentException(sprintf('Unknown status "%s"', $status));
        }

        $previous = $this
            ->connection
            ->fetchAssoc(
                'SELECT `status` FROM `storage_document` WHERE id = :id',
                [':id' => $documentId]
            );

        if (empty($previous)) {
            return 0;
        }

        return $this->update($documentId, ['status' => $status])
            ? $this->connection->insert(
                'storage_document_status',
                [
                    'document_id' => $documentId,
                    'status' => $previous['status']
                ]
            )
            : 0;
    }
}
