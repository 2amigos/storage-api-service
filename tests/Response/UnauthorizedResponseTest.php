<?php

namespace tests\Response;

use PHPUnit\Framework\TestCase;
use App\Application\Response\UnauthorizedResponse;

class UnauthorizedResponseTest extends TestCase
{
    public function testShouldBeProblemJsonAndHaveProperStatusCode(): void
    {
        $response = new UnauthorizedResponse('Dough!');

        $this->assertEquals('application/problem+json', $response->getHeaderLine('Content-type'));

        $this->assertEquals(401, $response->getStatusCode());
    }
}
