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
    protected $options;

    public function __construct(string $uri, MiddlewareInterface $currentMiddleware, array $options)
    {
        $this->uri = $uri;
        $this->currentMiddleware = $currentMiddleware;
        $this->options = $options;
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
        $resource = $this->getClientResource() + $resource;
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
        $query = $this->getClientQuery() + $query;
        return (string) http_build_query($query);
    }

    protected function buildPayload(array $payload) : array
    {
        $payload = $this->getClientPayload() + $payload;
        return $payload;
    }

    protected function buildHeaders(array $headers) : array
    {
        $headers = $this->getClientHeaders() + $headers;
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
        $curlOptionsArray = [
            CURLOPT_URL => $this->uri . $resourceString . '?' . $queryString,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $headersArray,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => $this->options[ClientInterface::PARAMETER_CONNECTION_TIMEOUT],
            CURLOPT_TIMEOUT => $this->options[ClientInterface::PARAMETER_TIMEOUT]
        ];
        $curlOptionsArray = $this->getClientCurlOptions() + $curlOptionsArray;

        $maxExecutionTime = (int)ini_get('max_execution_time');
        $combinedCurlTime = $this->options[ClientInterface::PARAMETER_CONNECTION_TIMEOUT] + $this->options[ClientInterface::PARAMETER_TIMEOUT];
        if (
            $maxExecutionTime !== 0 &&
            $maxExecutionTime < $combinedCurlTime
        ) {
            ini_set('max_execution_time', $combinedCurlTime + $maxExecutionTime);
        }

        switch ($verb) {
            case 'POST':
                $curlOptionsArray[CURLOPT_POST] = true;
                $curlOptionsArray[CURLOPT_POSTFIELDS] = $payloadArray;
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $curlOptionsArray[CURLOPT_CUSTOMREQUEST] = $verb;
                $curlOptionsArray[CURLOPT_POSTFIELDS] = $payloadArray;
                break;
        }

        return $this->currentMiddleware->process($curlOptionsArray);
    }

    abstract protected function getClientResource() : array;
    abstract protected function getClientQuery() : array;
    abstract protected function getClientHeaders() : array;
    abstract protected function getClientPayload() : array;
    abstract protected function getClientCurlOptions() : array;
}
