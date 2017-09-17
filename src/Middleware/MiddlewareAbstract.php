<?php
namespace RestClient\Middleware;

use RestClient\Middleware\MiddlewareInterface;

abstract class MiddlewareAbstract implements MiddlewareInterface
{
    protected $next;
    protected $options;

    public function __construct(MiddlewareInterface $next = null, array $options = [])
    {
        $this->next = $next;
        $this->options = $options;
    }

    abstract public function process(array $curlOptionsArray) : array;
}
