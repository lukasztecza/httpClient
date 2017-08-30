<?php
namespace RestClient\Client;

use RestClient\Client\ClientInterface;
use RestClient\Middleware\MiddlewareInterface;
use Request\Exception\WrongResourceException;
use Request\Exception\WrongHeadersException;

abstract class ClientAbstract implements ClientInterface
{
    protected $uri;
    protected $currentMiddleware;
    protected $connectTimeout;
    protected $timeout;

    public function __construct(string $uri, MiddlewareInterface $currentMiddleware, int $connectTimeout, int $timeout)
    {
        $this->uri = $uri;
        $this->currentMiddleware = $currentMiddleware;
        $this->connectTimeout = $connectTimeout;
        $this->timeout = $timeout;
    }

    public function get(array $resource = [], array $query = [], array $headers = []) : array
    {
        return $this->prepareAndCall(
            'GET',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers)
        );
    }

    public function post(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array
    {
        return $this->prepareAndCall(
            'POST',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers),
            $this->buildPayload($payload)
        );
    }

    public function put(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array
    {
        return $this->prepareAndCall(
            'PUT',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers),
            $this->buildPayload($payload)
        );
    }

    public function patch(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array
    {
        return $this->prepareAndCall(
            'PATCH',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers),
            $this->buildPayload($payload)
        );
    }

    public function delete(array $resource = [], array $query = [], array $headers = []) : array
    {
        return $this->prepareAndCall(
            'DELETE',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers)
        );
    }

    protected function buildResource(array $resource = []) : string
    {
        $resourceString = '';
        foreach ($resource as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $resourceString .= '/' . $key . '/' . $value;
            } elseif (is_string($key)) {
                $resourceString .= '/' . $key;
            } else {
                throw new WrongResourceException('Wrong resource array exception: ' . var_export($resource, true));
            }
        }
        return $resourceString;
    }

    protected function buildQuery(array $query) : string
    {
        return (string) http_build_query($query);
    }

    protected function buildPayload(array $payload) : array
    {
        return $payload;
    }

    protected function buildHeaders(array $headers) : array
    {
        $headersArray = [];
        foreach ($headers as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $headersArray[] .= $key . ': ' . $value;
            } else {
                throw new WrongHeadersException('Wrong headers array exception: ' . var_export($resource, true));
            }
        }
        return $headersArray;
    }

    protected function prepareAndCall(string $verb, string $resourceString, string $queryString, array $headersArray, array $payloadArray = []) : array
    {
        $curlOptArray = [
            CURLOPT_URL => $this->uri . $resourceString . '?' . $queryString,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $headersArray,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION =>  true
        ];

        switch ($verb) {
            case 'POST':
                $curlOptArray[CURLOPT_POST] = true;
                $curlOptArray[CURLOPT_POSTFIELDS] = $payloadArray;
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $curlOptArray[CURLOPT_CUSTOMREQUEST] = $verb;
                $curlOptArray[CURLOPT_POSTFIELDS] = $payloadArray;
                break;
        }

        return $this->currentMiddleware->process($curlOptArray);
    }
}
