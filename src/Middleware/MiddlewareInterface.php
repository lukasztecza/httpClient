<?php
namespace RestClient\Middleware;

interface MiddlewareInterface
{
    public function modifyRequestParameters($curlSession, array $requestParameters) : array;
    public function modifyResponseParameters(array $responseParameters) : array;
}
