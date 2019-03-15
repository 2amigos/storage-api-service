<?php

namespace App\Application\Document;


use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract
{
    public function transform(array $status)
    {
        return !empty($status)
            ? [
                'status' => DocumentStatusInterface::STATUS_LIST_STRING[$status['status']]
            ]
            : [];
    }
}