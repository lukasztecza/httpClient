<?php
namespace RestClient\Middleware;

use RestClient\Middleware\MiddlewareAbstract;

class JsonMiddleware extends MiddlewareAbstract
{
    public function process(array $curlOptionsArray) : array
    {
        $response = $this->next->process($curlOptionsArray);
        $response['json'] = $this->options;
        return $response;
    }
}
