<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Paginator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractPaginator implements PaginatorInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * AbstractPaginator constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $params
     * @return array
     */
    abstract public function paginate(QueryBuilder $queryBuilder, array $params): array;
}
