<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

final class DocumentSingleCommand
{
    /**
     * @var array
     */
    private $config;

    /**
     * DocumentStatusCommand constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->config['uuid'] = $params['uuid'];
        $this->config['tag'] = $params['tag'] ?? null;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->config;
    }
}
