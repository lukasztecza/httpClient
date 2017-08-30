<?php
namespace RestClient\Middleware;

use RestClient\Middleware\MiddlewareInterface;

abstract class MiddlewareAbstract implements MiddlewareInterface
{
    protected $next;

    public function __construct(MiddlewareInterface $next = null)
    {
        $this->next = $next;
    }

    abstract public function process(array $curlOptArray) : array;
}
