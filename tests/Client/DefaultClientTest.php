<?php

#namespace Tests\AppBundle\Controller;

use PHPUnit\Framework\TestCase;
use HttpClient\Client\DefaultClient;
use HttpClient\Middleware\MiddlewareInterface;

class DefaultClientTest extends TestCase
{
    private function callNonPublic()
    {

    }

    public function testBuildQuery()
    {
        $middlewareStub = $this->createMock(MiddlewareInterface::class);
        $defaultClient = new DefaultClient('http://google.com', $middlewareStub, []);
        $result = $defaultClient->buildQuery(['hey' => '1']);
        $this->assertEquals($result, 'hey=1');
    }
}
