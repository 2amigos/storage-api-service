<?php

namespace Tests\Document;

use Tests\BaseTest;
use App\Application\Document\DocumentStoreCommand;
use Slim\Http\UploadedFile;
use App\Application\Document\DocumentStatusInterface;

class DocumentStoreHandlerTest extends BaseTest
{

    public function testShouldReturnAValidResponse(): array
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

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
        $this->assertEquals(DocumentStatusInterface::UPLOADED, $data['status']);
        $this->assertIsString($data['uuid']);

        return $data;
    }

    /**
     * @depends testShouldReturnAValidResponse
     * @param array $response
     */
    public function testShouldStoreDocumentToTheDatabase(array $response): void
    {
        $query = self::$container->get('document.query');
        $document = $query->findOneByUuidAndTag($response['uuid']);

        $this->assertIsArray($document);
        $this->assertEquals($response['uuid'], $document['uuid']);
        $this->assertEquals(DocumentStatusInterface::UPLOADED, $document['status']);
    }

    /**
     * @depends testShouldReturnAValidResponse
     * @param array $response
     */
    public function testShouldUploadDocument(array $response): void
    {
        $query = self::$container->get('document.query');
        $document = $query->findOneByUuidAndTag($response['uuid']);

        $this->assertIsArray($document);
        $this->assertNotEmpty($document);

        $basePath = self::$container->get('settings')['storage']['local']['args']['root'];
        $filePath = $basePath . $document['name'];

        $this->assertFileExists($filePath);
    }

    public function testShouldSendFileToTheQueue()
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
            'async' => 'true',
            'document' => $document
        ];

        $commandBus = self::$container['commandBus'];
        $command = new DocumentStoreCommand($params, $document);
        $data = $commandBus->handle($command);

        $this->assertEquals(DocumentStatusInterface::PROCESSING, $data['status']);
    }
}