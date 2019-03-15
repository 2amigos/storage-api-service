<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use League\Fractal\TransformerAbstract;

class DocumentTransformer extends TransformerAbstract
{

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'statuses'
    ];

    public function transform(array $document)
    {
        return [
            'id' => (int)$document['id'],
            'uuid' => $document['uuid'],
            'name' => $document['name'],
            'status' => DocumentStatusInterface::STATUS_LIST_STRING[$document['status']],
            'storage' => $document['storage'],
            'tag' => $document['tag'] ?? null,
            'links' => [
                [
                    'rel' => 'self',
                    'link' => $document['path']
                ]
            ],
        ];
    }

    /**
     * @param array $document
     * @return \League\Fractal\Resource\Collection
     */
    public function includeStatuses(array $document)
    {
        return $this->collection($document['statuses'], new StatusTransformer);
    }
}
