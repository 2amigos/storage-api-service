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
use App\Infrastructure\Db\Document\DocumentQuery;
use Exception;

class DocumentStatusHandler
{
    /**
     * @var DocumentQuery
     */
    private $documentQuery;

    /**
     * DocumentStatusHandler constructor.
     * @param DocumentQuery $documentQuery
     */
    public function __construct(DocumentQuery $documentQuery)
    {
        $this->documentQuery = $documentQuery;
    }

    /**
     * @param DocumentStatusCommand $command
     * @throws DocumentNotFoundException
     * @throws DocumentStatusException
     * @return array
     */
    public function handle(DocumentStatusCommand $command)
    {
        try {
            $config = $command->getConfiguration();
            $product = $this->documentQuery->findOneByUuidAndTag($config['uuid'], $config['tag']);

            if (empty($product)) {
                throw new DocumentNotFoundException('Document not found');
            }

            return [
              'status' => $this->parseStatus((int)$product['status'])
            ];
        } catch (DocumentNotFoundException $e) {
            throw new $e;
        } catch (Exception $e) {
            throw new DocumentStatusException('Unable to fetch document', $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param int $status
     * @return mixed
     */
    private function parseStatus(int $status)
    {
        return DocumentStatusInterface::STATUS_LIST_STRING[$status];
    }
}
