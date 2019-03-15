<?php

namespace Tests\Document;

use App\Application\Document\DocumentStatusInterface;
use App\Application\Document\DocumentStoreCommand;
use App\Application\Document\Exception\DocumentStatusException;
use Slim\Http\UploadedFile;
use Tests\BaseTest;
use App\Application\Document\DocumentStatusCommand;

class DocumentStatusHandlerTest extends BaseTest
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

    public function testShouldFindDocumentAndItsStatus()
    {
        $commandBus = self::$container['commandBus'];
        $command = new DocumentStatusCommand(['uuid' => $this->document['uuid']]);
        $data = $commandBus->handle($command);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);

        $isStatusValid = \in_array($data['status'], DocumentStatusInterface::STATUS_LIST_STRING);
        $this->assertTrue($isStatusValid);
    }

    public function testNonExistingUuidShouldReturnException()
    {
        $this->expectException(DocumentStatusException::class);

        $nonExistingUuid = '';
        $commandBus = self::$container['commandBus'];
        $command = new DocumentStatusCommand(['uuid' => $nonExistingUuid]);
        $commandBus->handle($command);
    }

}