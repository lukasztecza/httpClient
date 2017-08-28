<?php
namespace RestClient\Middleware;

interface JsonMiddleware
{
    public function modifyRequestParameters($curlSession, array $requestParameters) : array
    {

    }

    public function modifyResponseParameters(array $responseParameters) : array
    {

    }
}
