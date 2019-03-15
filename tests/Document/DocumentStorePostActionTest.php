<?php

namespace Tests\Document;


use App\Application\Document\DocumentStatusInterface;
use App\Application\Token\CreateTokenCommand;
use App\Application\Token\CreateTokenHandler;
use GuzzleHttp\Client;
use Tests\BaseTest;
use GuzzleHttp\Exception\ClientException;

class DocumentStorePostActionTest extends BaseTest
{
    private $authToken;
    private $client;

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
    }

    public function testShouldReturnStoresValidationError()
    {
        try {
            $this->client->request('POST', 'http://localhost:8081/documents/store', [
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
            $this->assertEquals(400, $response->getStatusCode());
        }
    }

    public function testShouldReturnDocumentValidationError()
    {
        try {
            $this->client->request('POST', 'http://localhost:8081/documents/store', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ],
                'multipart' => [
                    [
                        'name'     => 'stores',
                        'contents' => 'local'
                    ]
                ]
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $this->assertEquals(400, $response->getStatusCode());
        }
    }

    public function testShouldReturnValidResponse()
    {
        $response = $this->client->request('POST', 'http://localhost:8081/documents/store', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken
            ],
            'multipart' => [
                [
                    'name'     => 'document',
                    'contents' => fopen(__DIR__ . '/../files/test.txt', 'r')
                ],
                [
                    'name'     => 'stores',
                    'contents' => 'local'
                ]
            ]
        ]);

        $response = json_decode($response->getBody(), true);

        $this->assertNotEmpty($response['data']);
        $this->assertIsArray($response['data']);
        $this->assertTrue($response['data']['success']);
        $this->assertEquals(DocumentStatusInterface::UPLOADED, $response['data']['status']);
        $this->assertIsString($response['data']['uuid']);
    }
}