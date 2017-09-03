<?php
namespace RestClient\Middleware;

use RestClient\Middleware\MiddlewareAbstract;

class JsonMiddleware extends MiddlewareAbstract
{
    public function process(array $curlOptArray) : array
    {
        $response = $this->next->process($curlOptArray);
        $response['json'] = $this->options;
        return $response;
    }
}
