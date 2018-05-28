<?php
use PHPUnit\Framework\TestCase;
use HttpClient\Client\DefaultClient;
use HttpClient\Middleware\MiddlewareInterface;

class DefaultClientTest extends TestCase
{
    protected $defaultClient;

    private function callNonPublic($object, string $method, array $params)
    {
        return (function () use ($object, $method, $params) {
            return call_user_func_array([$object, $method], $params);
        })->bindTo($object, $object)();
    }

    protected function setUp()
    {
        $middlewareStub = $this->createMock(MiddlewareInterface::class);
        $this->defaultClient = new DefaultClient('http://google.com', $middlewareStub, []);
    }

    protected function tearDown()
    {
    }

    public function testBuildResource()
    {
        $result = $this->callNonPublic($this->defaultClient, 'buildResource', ['resource' => [
            'book' => 3, 'parameter' => 'pages', 'list' => null
        ]]);
        $this->assertEquals($result, '/book/3/parameter/pages/list');
    }

    public function testBuildQuery()
    {
        $result = $this->callNonPublic($this->defaultClient, 'buildQuery', ['query' => ['queryParam' => 1]]);
        $this->assertEquals($result, 'queryParam=1');
    }

    public function testBuildPayload()
    {
        $result = $this->callNonPublic($this->defaultClient, 'buildPayload', ['payload' => ['formParam' => 2]]);
        $this->assertEquals($result, ['formParam' => 2]);
    }

    public function testBuildHeaders()
    {
        $result = $this->callNonPublic($this->defaultClient, 'buildHeaders', ['headers' => ['someHeader' => 3]]);
        $this->assertEquals($result, ['someHeader: 3']);
    }
}
