<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Db;

use App\Infrastructure\Paginator\CursorPaginator;
use App\Infrastructure\Paginator\PaginatorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractQuery implements QueryInterface
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * AbstractQuery constructor.
     * @param Connection $connection
     * @param PaginatorInterface $paginator
     */
    public function __construct(Connection $connection, PaginatorInterface $paginator)
    {
        $this->connection = $connection;
        $this->paginator = $paginator;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getOne(array $data): array
    {
        foreach ($data as $row) {
            return $row;
        }

        return [];
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $queryParams
     * @return array
     */
    protected function paginate(QueryBuilder $queryBuilder, array $queryParams): array
    {
        if ($this->paginator instanceof CursorPaginator) {
            $queryBuilder = $this->withCursorParams($queryBuilder, $queryParams);
        }

        return $this->paginator->paginate($queryBuilder, $queryParams);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $queryParams
     * @return QueryBuilder
     */
    private function withCursorParams(QueryBuilder $queryBuilder, array $queryParams): QueryBuilder
    {
        if (!empty($queryParams['cursor'][0]) && !empty($queryParams['cursor'][1])) {
            $queryBuilder->andWhere('sd.id >= :offset');
            $queryBuilder->setParameter(':offset', $queryParams['cursor'][1]);
            $queryBuilder->setMaxResults($queryParams['cursor'][0]);
        }

        return $queryBuilder;
    }
}
