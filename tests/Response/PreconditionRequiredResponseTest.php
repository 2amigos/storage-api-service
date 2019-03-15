<?php

namespace tests\Response;

use PHPUnit\Framework\TestCase;
use App\Application\Response\PreconditionRequiredResponse;

class PreconditionRequiredResponseTest extends TestCase
{
    public function testShouldBeProblemJsonAndHaveProperStatusCode(): void
    {
        $response = new PreconditionRequiredResponse('Dough!');

        $this->assertEquals('application/problem+json', $response->getHeaderLine('Content-type'));

        $this->assertEquals(428, $response->getStatusCode());
    }
}
