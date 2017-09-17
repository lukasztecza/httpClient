<?php
namespace RestClient\Middleware;

use RestClient\Middleware\MiddlewareAbstract;

class XmlMiddleware extends MiddlewareAbstract
{
    public function process(array $curlOptionsArray) : array
    {
        $response = $this->next->process($curlOptionsArray);
        $response['xml'] = $this->options;
        return $response;
    }
}
