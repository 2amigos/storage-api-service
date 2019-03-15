<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Application\Document\Exception\DocumentListException;
use App\Application\Response\InternalServerErrorResponse;
use App\Infrastructure\Slim\Actions\AbstractAction;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

final class DocumentListGetAction extends AbstractAction
{
    /**
     * DocumentListGetAction constructor.
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return InternalServerErrorResponse|mixed|Response
     */
    public function __invoke(Request $request, Response $response, array $args = [])
    {
        try {
            $command = new DocumentListCommand($args, $request);

            /**
             * @see DocumentStatusHandler::handle()
             * @throws DocumentListException
             */
            $data = $this->commandBus->handle($command);

            $resource = $this->createCollection(
                $data['items'],
                $request,
                new DocumentTransformer,
                null,
                $data['paginator']
            );
        } catch (DocumentListException $e) {
            return new InternalServerErrorResponse($e->getMessage());
        }

        return $this->renderJson($response, $resource, 201);
    }
}
