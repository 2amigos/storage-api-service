<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Db\Document;

use App\Infrastructure\Db\AbstractQuery;
use Doctrine\DBAL\Query\QueryBuilder;

final class DocumentQuery extends AbstractQuery
{
    /**
     * @var string
     */
    private $table = 'storage_document';

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * @param string $uuid
     * @param string|null $tag
     * @throws \Doctrine\DBAL\DBALException
     * @return array
     */
    public function findOneByUuidAndTag(string $uuid, string $tag = null): array
    {
        $sql = 'SELECT `id`, BIN_TO_UUID(uuid) AS `uuid`, `name`, `tag`, `status`, `storage`, `path` FROM `storage_document` WHERE  uuid = UUID_TO_BIN(:uuid) ';
        $params[':uuid'] = $uuid;

        if (!empty($tag)) {
            $sql .= 'AND tag = :tag ';
            $params[':tag'] = $tag;
        }

        return $this->connection
            ->fetchAssoc($sql, $params, [\PDO::PARAM_INT, \PDO::PARAM_INT]) ?: [];
    }

    /**
     * @param string|null $tag
     * @param array $queryParams
     * @return array
     */
    public function findAllByTag(string $tag = null, array $queryParams = []): array
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $queryBuilder->select('`id`, BIN_TO_UUID(uuid) AS `uuid`, `name`, `tag`, `status`, `storage`, `path`')
            ->from('storage_document AS sd');

        if (!empty($tag)) {
            $queryBuilder->where('tag = :tag');
            $queryBuilder->setParameter(':tag', $tag);
        }

        if (!empty($queryParams['order'][0]) && !empty($queryParams['order'][1])) {
            $queryBuilder->orderBy($queryParams['order'][0], $queryParams['order'][1]);
        }

        return $this->paginate($queryBuilder, $queryParams);
    }
}
