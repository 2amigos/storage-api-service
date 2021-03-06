<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Db;

interface RepositoryInterface
{
    /**
     * @param array $data
     * @return int
     */
    public function create(array $data): int;

    /**
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update(int $id, array $data): int;
}
