<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use Slim\Http\Request;

final class DocumentListCommand
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var Request
     */
    private $request;

    /**
     * DocumentListCommand constructor.
     * @param array $params
     * @param Request $request
     */
    public function __construct(array $params, Request $request)
    {
        $this->config['tag'] = $params['tag'] ?? null;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->config;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
