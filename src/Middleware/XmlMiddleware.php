<?php
namespace RestClient\Middleware;

use RestClient\Middleware\MiddlewareInterface;

class XmlMiddleware implements MiddlewareInterface
{
    private $next;

    public function __construct(MiddlewareInterface $next)
    {
        $this->next = $next;
    }

    public function process(array $curlOptArray) : array
    {
        $response = $this->next->process($curlOptArray);
        $response['xml'] = 'ok';
        return $response;
    }
}
