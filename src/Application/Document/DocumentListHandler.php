<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use App\Infrastructure\Db\Document\DocumentQuery as DocumentQueryAlias;
use App\Infrastructure\Db\Document\DocumentQuery;
use App\Infrastructure\Paginator\PagerfantaPaginator;
use App\Infrastructure\Paginator\QueryParametersParser;
use App\Infrastructure\Db\Document\DocumentStatusQuery;

class DocumentListHandler
{
    /**
     * @var DocumentQuery
     */
    private $documentQuery;
    /**
     * @var DocumentStatusQuery
     */
    private $documentStatusQuery;
    /**
     * @var QueryParametersParser
     */
    private $queryParametersParser;

    /**
     * DocumentListHandler constructor.
     * @param DocumentQuery $documentQuery
     * @param DocumentStatusQuery $documentStatusQuery
     * @param QueryParametersParser $queryParametersParser
     */
    public function __construct(
        DocumentQuery $documentQuery,
        DocumentStatusQuery $documentStatusQuery,
        QueryParametersParser $queryParametersParser
    ) {
        $this->documentQuery = $documentQuery;
        $this->documentStatusQuery = $documentStatusQuery;
        $this->queryParametersParser = $queryParametersParser;
    }

    /**
     * @param DocumentListCommand $command
     * @return array
     */
    public function handle(DocumentListCommand $command): array
    {
        $config = $command->getConfiguration();
        $queryParams = $this->queryParametersParser->parse($command->getRequest());
        $data = $this->documentQuery->findAllByTag($config['tag'], $queryParams);

        if (isset($_GET['include'])) {
            $data['items'] = $this->getItemsWithStatuses($data['items']);
        }

        return $data;
    }

    /**
     * @param array $items
     * @return array
     */
    private function getItemsWithStatuses(array $items): array
    {
        $documentIds = array_column($items, 'id');
        $statuses = $this->documentStatusQuery->findAllByDocumentIds($documentIds);

        return array_map(function($item) use ($statuses) {
            $documentStatuses = array_filter($statuses, function($status) use ($item) {
                return $item['id'] === $status['document_id'] ? $status : null;
            });

            $item['statuses'] = $documentStatuses;
            return $item;
        }, $items);
    }
}
