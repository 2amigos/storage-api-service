<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Paginator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use League\Fractal\Pagination\Cursor;

final class CursorPaginator extends AbstractPaginator
{

    /**
     * PagerfantaPaginator constructor.
     * @param Connection $connection
     */
    public function __construct(
        Connection $connection
    ) {
        parent::__construct($connection);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $params
     * @return array
     */
    public function paginate(QueryBuilder $queryBuilder, array $params): array
    {
        $cursor = new Cursor();
        $data = $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        $cursorField = $this->getId();
        $cursor->setCurrent($data[0][$cursorField]);
        $cursor->setCount(\count($data));
        $lastRow = end($data);
        $cursor->setNext($lastRow[$cursorField]);

        return [
          'items' => $data,
          'paginator' => $cursor
        ];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'id';
    }

    /**
     * @return string
     */
    public function getQueryParam(): string
    {
        return 'cursor';
    }
}
