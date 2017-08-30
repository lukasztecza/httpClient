<?php
namespace RestClient\Middleware;

interface MiddlewareInterface
{
    public function __construct(MiddlewareInterface $next = null);
    public function process(array $curlOptArray) : array;
}
