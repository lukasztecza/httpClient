<?php
namespace RestClient\Client;

use RestClient\Middleware\MiddlewareInterface;

interface ClientInterface
{
    const PARAMETER_URI = 'uri';
    const PARAMETER_CLASS = 'class';
    const PARAMETER_OPTIONS = 'options';
    const PARAMETER_MIDDLEWARES = 'middlewares';
    const PARAMETER_CONNECTION_TIMEOUT = 'connectionTimeout';
    const PARAMETER_TIMEOUT = 'timeout';

    const DEFAULT_CLIENT_CLASS = 'RestClient\Client\DefaultClient';
    const CURL_MIDDLEWARE_CLASS = 'RestClient\Middleware\CurlMiddleware';
    const DEFAULT_CONNECTION_TIMEOUT = 5;
    const DEFAULT_TIMEOUT = 5;
    const CLIENT_OBJECT = 'client';

    public function __construct(string $uri, MiddlewareInterface $currentMiddleware, array $options);
    public function get(array $resource = [], array $query = [], array $headers = []) : array;
    public function post(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;
    public function put(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;
    public function patch(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;
    public function delete(array $resource = [], array $query = [], array $headers = []) : array;
}
