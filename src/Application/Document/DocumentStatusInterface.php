<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

interface DocumentStatusInterface
{
    public const PENDING = 1;
    public const PROCESSING = 2;
    public const UPLOADED = 3;
    public const FAILED = 4;
    public const DELETED = 5;

    public const PENDING_STRING = 'pending';
    public const PROCESSING_STRING = 'processing';
    public const UPLOADED_STRING = 'uploaded';
    public const FAILED_STRING = 'failed';
    public const DELETED_STRING = 'deleted';

    public const STATUS_LIST = [
        self::PENDING,
        self::PROCESSING,
        self::UPLOADED,
        self::FAILED,
        self::DELETED,
    ];

    public const STATUS_LIST_STRING = [
        self::PENDING => self::PENDING_STRING,
        self::PROCESSING => self::PROCESSING_STRING,
        self::UPLOADED => self::UPLOADED_STRING,
        self::FAILED => self::FAILED_STRING,
        self::DELETED => self::DELETED_STRING
    ];
}
