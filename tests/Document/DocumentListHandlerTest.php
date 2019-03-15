<?php

namespace Tests\Document;

use App\Application\Document\DocumentListCommand;
use League\Fractal\Pagination\Cursor;
use Pagerfanta\Pagerfanta;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Uri;
use Tests\BaseTest;
use Tests\TestDataFactory;

class DocumentListHandlerTest extends BaseTest
{
    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        TestDataFactory::generateDocuments(20);
    }

    /**
     * @param string $query
     */
    public function setUpRequest(string $query)
    {
        $uri = new Uri(
            'http',
            'localhost',
            8080,
            '/documents/list',
            $query
        );
        $headers = new Headers();
        $body = new RequestBody();
        $this->request = new Request('GET', $uri, $headers, [], [], $body);
    }

    public function testShouldReturnDocumentListPagerfantaDefault()
    {
        $this->setUpRequest('');

        $commandBus = self::$container['commandBus'];
        $command = new DocumentListCommand([], $this->request);
        $data = $commandBus->handle($command);

        $isPagerfantaAdapter = $data['paginator'] instanceof Pagerfanta;
        $this->assertTrue($isPagerfantaAdapter);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('paginator', $data);
        $this->assertCount(10, $data['items']);
    }

    public function testShouldReturnDocumentListPagerfantaLimit()
    {
        $this->setUpRequest('filter=limit(5|1)');

        $commandBus = self::$container['commandBus'];
        $command = new DocumentListCommand([], $this->request);
        $data = $commandBus->handle($command);

        $isPagerfantaAdapter = $data['paginator'] instanceof Pagerfanta;
        $this->assertTrue($isPagerfantaAdapter);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('paginator', $data);
        $this->assertCount(5, $data['items']);
    }

    // Uncomment if you are using Cursor for pagination
    /*
    public function testShouldReturnDocumentListCursorDefault()
    {
        $this->setUpRequest('');

        $commandBus = self::$container['commandBus'];
        $command = new DocumentListCommand([], $this->request);

        $data = $commandBus->handle($command);

        $isCursor = $data['paginator'] instanceof Cursor;
        $this->assertTrue($isCursor);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('paginator', $data);
        $this->assertCount(20, $data['items']);
    }

    public function testShouldReturnDocumentListPagerfantaLimit()
    {
        $this->setUpRequest('filter=cursor(5|1)');

        $commandBus = self::$container['commandBus'];
        $command = new DocumentListCommand([], $this->request);

        $data = $commandBus->handle($command);

        $isCursor = $data['paginator'] instanceof Cursor;
        $this->assertTrue($isCursor);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('paginator', $data);
        $this->assertCount(5, $data['items']);
    }
    */
}