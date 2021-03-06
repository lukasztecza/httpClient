<?php
namespace HttpClient\Client;

use HttpClient\Middleware\MiddlewareInterface;

interface ClientInterface
{
    public function __construct(string $uri, MiddlewareInterface $currentMiddleware, array $options);

    public function get(array $resource = [], array $query = [], array $headers = []) : array;

    public function post(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;

    public function put(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;

    public function patch(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;

    public function delete(array $resource = [], array $query = [], array $headers = []) : array;
}
