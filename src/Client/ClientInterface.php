<?php
namespace RestClient\Client;

interface ClientInterface
{
    const PARAMETER_URI = 'uri';
    const PARAMETER_CLIENT_CLASS = 'class';
    const PARAMETER_MIDDLEWARES_ARRAY = 'middlewaresArray';
    const PARAMETER_CONNECTION_TIMEOUT = 'connectionTimeout';
    const PARAMETER_TIMEOUT = 'timeout';
    const PARAMETER_CLIENT = 'client';

    const DEFAULT_CLIENT_CLASS = 'RestClient\Client\DefaultClient';
    const DEFAULT_CONNECTION_TIMEOUT = 5;
    const DEFAULT_TIMEOUT = 5;

    public function get(array $resource = [], array $query = [], array $headers = []) : array;
    public function post(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;
    public function put(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;
    public function patch(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array;
    public function delete(array $resource = [], array $query = [], array $headers = []) : array;
}
