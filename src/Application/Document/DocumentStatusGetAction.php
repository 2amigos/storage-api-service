<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Application\Document\Exception\DocumentNotFoundException;
use App\Application\Document\Exception\DocumentStatusException;
use App\Application\Response\InternalServerErrorResponse;
use App\Application\Response\NotFoundResponse;
use App\Infrastructure\Slim\Actions\AbstractAction;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class DocumentStatusGetAction extends AbstractAction
{
    /**
     * DocumentStatusGetAction constructor.
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
     * @return InternalServerErrorResponse|NotFoundResponse|mixed|\Psr\Http\Message\ResponseInterface|Response
     */
    public function __invoke(Request $request, Response $response, array $args = [])
    {
        try {
            $command = new DocumentStatusCommand($args);

            /**
             * @see DocumentStatusHandler::handle()
             * @throws DocumentStatusException|DocumentNotFoundException
             */
            $data = $this->commandBus->handle($command);
            $data = $this->createItem($data);
        } catch (DocumentNotFoundException $e) {
            return new NotFoundResponse($e->getMessage());
        } catch (DocumentStatusException $e) {
            return new InternalServerErrorResponse($e->getMessage());
        }

        return $this->renderJson($response, $data, 201);
    }
}
