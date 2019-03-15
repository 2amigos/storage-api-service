<?php

namespace tests\Response;

use PHPUnit\Framework\TestCase;
use App\Application\Response\ForbiddenResponse;

class ForbiddenResponseTest extends TestCase
{

    public function testShouldBeProblemJsonAndHaveProperStatusCode(): void
    {
        $response = new ForbiddenResponse('Dough!');

        $this->assertEquals('application/problem+json', $response->getHeaderLine('Content-Type'));

        $this->assertEquals(403, $response->getStatusCode());
    }
}
