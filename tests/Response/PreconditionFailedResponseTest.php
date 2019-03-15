<?php

namespace tests\Response;

use PHPUnit\Framework\TestCase;
use App\Application\Response\PreconditionFailedResponse;

class PreconditionFailedResponseTest extends TestCase
{
    public function testShouldBeProblemJsonAndHaveProperStatusCode(): void
    {
        $response = new PreconditionFailedResponse('Dough!');
        $this->assertEquals('application/problem+json', $response->getHeaderLine('Content-type'));
        $this->assertEquals(412, $response->getStatusCode());
    }
}
