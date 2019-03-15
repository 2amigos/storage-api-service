<?php

namespace tests\Response;

use PHPUnit\Framework\TestCase;
use App\Application\Response\NotFoundResponse;

class NotFoundResponseTest extends TestCase
{
    public function testShouldBeProblemJsonAndHaveProperStatusCode(): void
    {
        $response = new NotFoundResponse('Dough!');

        $this->assertEquals('application/problem+json', $response->getHeaderLine('Content-Type'));

        $this->assertEquals(404, $response->getStatusCode());
    }
}
