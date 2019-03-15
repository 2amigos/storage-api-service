<?php

namespace Tests\Document;


use App\Application\Document\DocumentStatusInterface;
use App\Application\Document\DocumentStoreCommand;
use App\Application\Token\CreateTokenCommand;
use App\Application\Token\CreateTokenHandler;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Slim\Http\UploadedFile;
use Tests\BaseTest;
use GuzzleHttp\Exception\ClientException;

class DocumentSingleGetActionTest extends BaseTest
{
    private $authToken;
    private $client;
    private $document;

    public function setUp()
    {
        $config = [
            'php.auth.user' => null,
            'requested.scopes' => ['document.all'],
            'lifespan' => 'now +1 hour'
        ];

        $command = new CreateTokenCommand($config);
        $data = (new CreateTokenHandler($config['requested.scopes']))->handle($command);

        $this->authToken = $data['token'];
        $this->client = new Client();

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

    public function testShouldReturnFileToDownload()
    {
        $response = $this->client->request('GET', 'http://localhost:8081/documents/' . $this->document['uuid'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $headers = $response->getHeaders();
        $this->assertIsArray($headers['Content-Disposition']);

        $data = explode(';', $headers['Content-Disposition'][0]);
        $this->assertEquals('attachment', $data[0]);
    }

    public function testShouldReturnNotFoundError()
    {
        $nonExistingUuid = Uuid::uuid4()->toString();

        try {
            $this->client->request('GET', 'http://localhost:8081/documents/' . $nonExistingUuid, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ],
                'multipart' => [
                    [
                        'name'     => 'document',
                        'contents' => fopen(__DIR__ . '/../files/test.txt', 'r')
                    ]
                ]
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $this->assertEquals(404, $response->getStatusCode());
        }
    }

}