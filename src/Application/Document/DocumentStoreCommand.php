<?php declare(strict_types=1);

/*
 * This file is part of the 2amigos/storage-service.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Application\Document;

use Slim\Http\UploadedFile;

final class DocumentStoreCommand
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var int|null
     */
    private $documentId = null;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string|null
     */
    private $uuid = null;
    /**
     * @var integer
     */
    private $status;
    /**
     * @var string
     */
    private $pathPrefix;

    /**
     * SendMessageCommand constructor.
     * @param array $params
     * @param UploadedFile $document
     */
    public function __construct(array $params, UploadedFile $document)
    {
        $this->config['params'] = $params;
        $this->config['document'] = $document;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->config;
    }

    /**
     * @return string|null
     */
    public function getTag(): ?string
    {
        return $this->config['params']['tag'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getStorage(): ?string
    {
        return $this->config['params']['stores'];
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return '/documents/' . $this->getUuid();
    }

    /**
     * @return bool
     */
    public function isAsync(): bool
    {
        return filter_var($this->config['params']['async'], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @throws \Exception
     */
    public function generateName(): void
    {
        $document = $this->config['document'];
        $extension = pathinfo($document->getClientFilename(), PATHINFO_EXTENSION);

        $this->name = !empty($this->config['params']['name'])
            ? trim(preg_replace('/[^a-z0-9_.]+/', '-', strtolower($this->config['params']['name'])), '-')
            : bin2hex(random_bytes(16)) . '.' . $extension;
    }

    /**
     * @return UploadedFile
     */
    public function getDocument(): UploadedFile
    {
        return $this->config['document'];
    }

    /**
     * @param int $documentId
     */
    public function setDocumentId(int $documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * @return int|null
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param string $uuid
     */
    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}
