<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Infrastructure\Slim\Actions;

use App\Infrastructure\Paginator\QueryParametersParser;
use Closure;
use League\Fractal\Manager;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use League\Tactician\CommandBus;
use Pagerfanta\Pagerfanta;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class AbstractAction
{
    /**
     * @var CommandBus
     */
    protected $commandBus;
    /**
     * @var Manager
     */
    protected $fractal;

    /**
     * AbstractAction constructor.
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->commandBus = $ci->get('commandBus');
        $this->fractal = $ci->get('fractal');
    }

    /**
     * @param Request|ServerRequestInterface $request
     * @param Response|ResponseInterface $response
     * @param array $args
     * @return mixed
     */
    abstract public function __invoke(Request $request, Response $response, array $args = []);

    /**
     * Render a JSON response
     *
     * @param Response $response Slim App Response
     * @param  mixed $data The data
     * @param  int $status The HTTP status code.
     * @param  int $encodingOptions Json encoding options
     *
     * @return Response
     */
    protected function renderJson(
        Response $response,
        $data,
        int $status = null,
        int $encodingOptions = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    ): ResponseInterface {
        return $response->withJson($data, $status, $encodingOptions);
    }

    /**
     * @param $data
     * @param TransformerAbstract|Closure|null $callback
     * @param string|null $namespace
     * @return array
     */
    protected function createItem($data, $callback = null, string $namespace = null): array
    {
        $callback = $callback ?? function ($data) {
            return $data;
        };

        return $this->fractal->createData(new Item($data, $callback, $namespace))->toArray();
    }

    /**
     * @param array $data
     * @param $request
     * @param null $callback
     * @param string|null $namespace
     * @param null $paginator
     * @return array
     */
    protected function createCollection(array $data, $request, $callback = null, string $namespace = null, $paginator = null): array
    {
        if (null === $callback || !$callback instanceof TransformerAbstract || !is_callable([$callback, 'transform'])) {
            $callback = function ($data) {
                return $data;
            };
        }

        $resource = new Collection($data, $callback, $namespace);
        if (!empty($paginator)) {
            switch (true) {
                case $paginator instanceof Pagerfanta:
                    $paginatorAdapter = new PagerfantaPaginatorAdapter($paginator, function(int $page) use ($request) {
                        $basePath = $request->getUri()->getBaseUrl();
                        $path = $request->getUri()->getPath();
                        $newRoute = $basePath . $path . $this->getUpdatedQueryParams($request, $page);
                        return $newRoute;
                    });

                    $resource->setPaginator($paginatorAdapter);
                    break;
                case $paginator instanceof CursorInterface:
                    $resource->setCursor($paginator);
                    break;
            }
        }

        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * @param Response $response
     * @param array $data
     * @return Response
     */
    protected function outputFile(Response $response, array $data): Response
    {
        return $response
            ->withHeader('Accept-Ranges', 'bytes')
            ->withHeader('Content-Type', $data['mime_type'])
            ->withHeader('Content-Length', \strlen($data['file']))
            ->withHeader('Content-Disposition', 'attachment; filename="' . $data['meta']['path'] . '"')
            ->withHeader('Expires', '-1')
            ->withHeader('Cache-Control', 'public, must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Pragma', 'public')
            ->write($data['file']);
    }

    /**
     * @param Request $request
     * @param int $page
     * @return string
     */
    private function getUpdatedQueryParams(Request $request, int $page)
    {
        $queryString = '?';

        foreach ($request->getQueryParams() as $key => $params) {
            $parsedArray = QueryParametersParser::parse($request);

            if ($key === 'filter') {
                $queryString .= $key . '=';
                $lastElement = end($parsedArray);
                foreach ($parsedArray as $parsedKey => $parsed) {
                    $first = $parsed[0];
                    $second = $parsedKey === 'limit' ? $page : $parsed[1];

                    $queryString .= $parsedKey .'(' . $first . '|' . $second . ')';
                    if ($parsed !== $lastElement) {
                        $queryString .= ':';
                    }
                }
            } else {
                $queryString .= '&' . $key .'='. $params;
            }

        }

        return $queryString;
    }
}
