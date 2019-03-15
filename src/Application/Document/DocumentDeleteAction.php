<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Application\Document\Exception\DocumentDeleteException;
use App\Application\Document\Exception\DocumentNotFoundException;
use App\Application\Response\InternalServerErrorResponse;
use App\Application\Response\NotFoundResponse;
use App\Infrastructure\Slim\Actions\AbstractAction;
use Exception;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class DocumentDeleteAction extends AbstractAction
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
     * @return InternalServerErrorResponse|NotFoundResponse|mixed|Response
     */
    public function __invoke(Request $request, Response $response, array $args = [])
    {
        try {
            $command = new DocumentDeleteCommand($args);

            /**
             * @see DocumentStatusHandler::handle()
             * @throws DocumentNotFoundException|DocumentDeleteException
             */
            $data = $this->commandBus->handle($command);
        } catch (DocumentNotFoundException $e) {
            return new NotFoundResponse($e->getMessage());
        } catch (DocumentDeleteException $e) {
            return new InternalServerErrorResponse($e->getMessage());
        }

        return $this->renderJson($response, $data);
    }
}
