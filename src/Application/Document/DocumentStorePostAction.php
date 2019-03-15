<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Application\Document\Exception\StoreDocumentException;
use App\Application\Response\InternalServerErrorResponse;
use App\Infrastructure\Slim\Actions\AbstractAction;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class DocumentStorePostAction extends AbstractAction
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return InternalServerErrorResponse|mixed|\Psr\Http\Message\ResponseInterface|Response
     */
    public function __invoke(Request $request, Response $response, array $args = [])
    {
        try {
            $files = $request->getUploadedFiles();
            $command = new DocumentStoreCommand($request->getParsedBody(), $files['document']);

            /**
             * @see DocumentStoreHandler::handle()
             * @throws StoreDocumentException
             */
            $data = $this->commandBus->handle($command);
            $data = $this->createItem($data);
        } catch (StoreDocumentException $exception) {
            return new InternalServerErrorResponse('Unable to store document');
        }

        return $this->renderJson($response, $data, 201);
    }
}
