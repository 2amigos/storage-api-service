<?php

namespace tests\Response;

use PHPUnit\Framework\TestCase;
use App\Application\Response\InternalServerErrorResponse;

class InternalServerErrorResponseTest extends TestCase
{
    public function testShouldBeProblemJsonAndHaveProperStatusCode(): void
    {
        $response = new InternalServerErrorResponse('Dough!');

        $this->assertEquals('application/problem+json', $response->getHeaderLine('Content-Type'));

        $this->assertEquals(501, $response->getStatusCode());
    }
}
