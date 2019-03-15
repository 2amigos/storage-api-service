<?php

namespace Tests\Document;


use App\Application\Document\DocumentSingleCommand;
use App\Application\Document\DocumentStoreCommand;
use App\Application\Document\Exception\DocumentNotFoundException;
use Ramsey\Uuid\Uuid;
use Slim\Http\UploadedFile;
use Tests\BaseTest;

class DocumentSingleHandlerTest extends BaseTest
{
    private $document;

    public function setUp()
    {
        $document = new UploadedFile(
            __DIR__ . '/../files/test.txt',
            'test.txt',
            'text/plain',
            filesize(__DIR__ . '/../files/test.txt')
        );

        $params = [
            'name' => null,
            'stores' => 'local',
            'async' => 'false',
            'document' => $document
        ];

        $commandBus = self::$container['commandBus'];
        $command = new DocumentStoreCommand($params, $document);
        $data = $commandBus->handle($command);

        $this->document = $data;
    }

    public function testShouldFindValidFileToDownload()
    {
        $commandBus = self::$container['commandBus'];
        $command = new DocumentSingleCommand(['uuid' => $this->document['uuid']]);
        $data = $commandBus->handle($command);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('file', $data);
        $this->assertArrayHasKey('mime_type', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('type', $data['meta']);
        $this->assertArrayHasKey('path', $data['meta']);
        $this->assertArrayHasKey('timestamp', $data['meta']);
        $this->assertArrayHasKey('size', $data['meta']);
        $this->assertEquals('This is a test text file.', $data['file']);
        $this->assertEquals('text/plain', $data['mime_type']);
    }

    public function testShouldReturnNotFoundException()
    {
        $this->expectException(DocumentNotFoundException::class);

        $nonExistingUuid = Uuid::uuid4()->toString();

        $commandBus = self::$container['commandBus'];
        $command = new DocumentSingleCommand(['uuid' => $nonExistingUuid]);
        $commandBus->handle($command);
    }
}