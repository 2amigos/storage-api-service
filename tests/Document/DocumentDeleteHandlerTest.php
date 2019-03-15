<?php

namespace Tests\Document;


use App\Application\Document\DocumentStatusInterface;
use App\Application\Document\DocumentStoreCommand;
use Slim\Http\UploadedFile;
use Tests\BaseTest;
use App\Application\Document\DocumentDeleteCommand;

class DocumentDeleteHandlerTest extends BaseTest
{

    public function testShouldReturnValidResponse()
    {
        $documentFile = new UploadedFile(
            __DIR__ . '/../files/test.txt',
            'test.txt',
            'text/plain',
            filesize(__DIR__ . '/../files/test.txt')
        );

        $params = [
            'name' => null,
            'stores' => 'local',
            'async' => 'false',
            'document' => $documentFile
        ];

        $commandBus = self::$container['commandBus'];
        $command = new DocumentStoreCommand($params, $documentFile);
        $uploadedDocument = $commandBus->handle($command);

        $query = self::$container->get('document.query');
        $documentDb = $query->findOneByUuidAndTag($uploadedDocument['uuid']);

        $this->assertFileExists($documentDb['path']);

        $commandBus = self::$container['commandBus'];
        $command = new DocumentDeleteCommand(['uuid' => $uploadedDocument['uuid']]);
        $data = $commandBus->handle($command);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        return $uploadedDocument;
    }

    /**
     * @param array $uploadedDocument
     * @depends testShouldReturnValidResponse
     */
    public function testShouldChangeDatabaseStatusToDeleted(array $uploadedDocument)
    {
        $query = self::$container->get('document.query');
        $document = $query->findOneByUuidAndTag($uploadedDocument['uuid']);

        $this->assertEquals($uploadedDocument['uuid'], $document['uuid']);
        $this->assertEquals(DocumentStatusInterface::DELETED, $document['status']);
    }

    /**
     * @param array $uploadedDocument
     * @depends testShouldReturnValidResponse
     */
    public function testShouldDeleteDocumentFile(array $uploadedDocument)
    {
        $query = self::$container->get('document.query');
        $document = $query->findOneByUuidAndTag($uploadedDocument['uuid']);

        $this->assertFileNotExists($document['path']);
    }

}