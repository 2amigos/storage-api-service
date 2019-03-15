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
use Pagerfanta\Adapter\DoctrineDbalSingleTableAdapter;
use Pagerfanta\Pagerfanta;

final class PagerfantaPaginator extends AbstractPaginator
{

    private $router;

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
        $countField = 'sd.id';
        $adapter = new DoctrineDbalSingleTableAdapter($queryBuilder, $countField);
        $paginator = new Pagerfanta($adapter);

        if (!empty($params['limit'][0]) && !empty($params['limit'][1])) {
            $paginator->setMaxPerPage($params['limit'][0]); // 10 by default
            $paginator->setCurrentPage($params['limit'][1]); // 1 by default
        }

        $items = $paginator->getCurrentPageResults();

        return [
            'items' => $items,
            'paginator' => $paginator
        ];
    }
}
