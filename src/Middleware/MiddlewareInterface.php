<?php
namespace RestClient\Middleware;

interface MiddlewareInterface
{
    public function process(array $curlOptArray) : array;
}
