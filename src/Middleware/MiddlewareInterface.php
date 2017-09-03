<?php
namespace RestClient\Middleware;

interface MiddlewareInterface
{
    public function __construct(MiddlewareInterface $next = null, array $options = []);
    public function process(array $curlOptArray) : array;
}
