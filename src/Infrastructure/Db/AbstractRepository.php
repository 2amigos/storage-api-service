<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Db;

use Doctrine\DBAL\Connection;

abstract class AbstractRepository
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * AbstractMySqlDbQuery constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
}
