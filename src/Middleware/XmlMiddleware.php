<?php
namespace RestClient\Middleware;

use RestClient\Middleware\MiddlewareAbstract;

class XmlMiddleware extends MiddlewareAbstract
{
    public function process(array $curlOptArray) : array
    {
        $response = $this->next->process($curlOptArray);
        $response['xml'] = 'ok';
        return $response;
    }
}
