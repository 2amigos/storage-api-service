<?php

namespace App\Infrastructure\Db\Document;


use App\Infrastructure\Db\AbstractQuery;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;

final class DocumentStatusQuery extends AbstractQuery
{
    /**
     * @var string
     */
    private $table = 'storage_document_status';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * @param array $documentIds
     * @return array
     */
    public function findAllByDocumentIds(array $documentIds): array
    {
        $qb = new QueryBuilder($this->connection);

        $qb
            ->select('id, document_id, status')
            ->from($this->table)
            ->andWhere('document_id IN (:document_ids)')
            ->setParameter(':document_ids', $documentIds, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        return $qb->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }
}