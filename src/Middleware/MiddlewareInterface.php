<?php
namespace HttpClient\Middleware;

interface MiddlewareInterface
{
    public function __construct(MiddlewareInterface $next = null, array $options = []);

    public function process(array $curlOptionsArray) : array;
}
