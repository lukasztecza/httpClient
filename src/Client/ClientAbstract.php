<?php
namespace RestClient\Client;

use RestClient\Client\ClientInterface;
use Request\Exception\WrongResourceException;
use Request\Exception\WrongHeadersException;

abstract class ClientAbstract implements ClientInterface
{
    protected $uri;
    protected $middlewaresArray;
    protected $connectTimeout;
    protected $timeout;

    public function __construct(string $uri, array $middlewaresArray, int $connectTimeout, int $timeout)
    {
        $this->uri = $uri;
        $this->middlewaresArray = $middlewaresArray;
        $this->connectTimeout = $connectTimeout;
        $this->timeout = $timeout;
    }

    public function get(array $resource = [], array $query = [], array $headers = []) : array
    {
        return $this->callCurl(
            'GET',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers)
        );
    }

    public function post(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array
    {
        return $this->callCurl(
            'POST',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers),
            $this->buildPayload($payload)
        );
    }

    public function put(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array
    {
        return $this->callCurl(
            'PUT',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers),
            $this->buildPayload($payload)
        );
    }

    public function patch(array $resource = [], array $query = [], array $headers = [], array $payload = []) : array
    {
        return $this->callCurl(
            'PATCH',
            $this->buildResource($resource),
            $this->buildQuery($query),
            $this->buildHeaders($headers),
            $this->buildPayload($payload)
        );
    }

    public function delete(array $resource = [], array $query = [], array $headers = []) : array
    {
        return $this->callCurl(
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

    protected function callCurl(string $verb, string $resourceString, string $queryString, array $headersArray, array $payloadArray = []) : array
    {
        $curlSession = curl_init();

        $requestParameters = [
            'resourceString' => $resourceString,
            'queryString' => $queryString,
            'headersArray' => $headersArray,
            'payloadArray' => $payloadArray
        ];
        $this->setRequestParameters($curlSession, $requestParameters, $verb);
        foreach ($this->middlewaresArray as $middleware) {
            call_user_func_array([$middleware, 'modifyRequestParameters'], [$curlSession, $requestParameters]);
        }

        $response = curl_exec($curlSession);

        $responseHeaderSize = curl_getinfo($curlSession, CURLINFO_HEADER_SIZE);
        $responseHeader = substr($response, 0, $responseHeaderSize);
        curl_close($curlSession);

        $headers = $this->getHeadersFromResponse($responseHeader);
        $body = substr($response, $responseHeaderSize);
        $responseParameters = [
            'headers' => $headers,
            'body' => $body
        ];
        $this->setResponseParameters($responseParameters);
        for ($i = count($this->middlewaresArray) - 1; $i >= 0; $i--) {
            call_user_func_array([$this->middlewaresArray[$i], 'modifyResponseParameters'], [$responseParameters]);
        }

        return $responseParameters;
    }

    protected function setRequestParameters($curlSession, array $requestParameters, string $verb)
    {
        curl_setopt(
            $curlSession,
            CURLOPT_URL,
            $this->uri . $requestParameters['resourceString'] . '?' . $requestParameters['queryString']
        );
        curl_setopt($curlSession, CURLOPT_HEADER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_FOLLOWLOCATION, true);

        $headers = [];
        foreach ($requestParameters['headersArray'] as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        curl_setopt($curlSession, CURLOPT_HTTPHEADER, $headers);

        switch ($verb) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($curlSession, CURLOPT_POST, 1);
                curl_setopt($curlSession, CURLOPT_POSTFIELDS,$requestParameters['payloadArray']);
                break;
            case 'PUT':
                curl_setopt($curlSession, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curlSession, CURLOPT_POSTFIELDS,$requestParameters['payloadArray']);
                break;
            case 'PATCH':
                curl_setopt($curlSession, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($curlSession, CURLOPT_POSTFIELDS,$requestParameters['payloadArray']);
                break;
            case 'DELETE':
                curl_setopt($curlSession, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }
    }

    protected function getHeadersFromResponse(string $responseHeader) : array
    {
        $headers = [];
        $headersArray = explode("\r\n\r\n", $responseHeader);
        foreach ($headersArray as $headerBlock) {
            if (empty($headerBlock)) {
                continue;
            }
            $linesArray = explode("\r\n", $headerBlock);
            $filteredHeaders = [];
            foreach ($linesArray as $index => $line) {
                $firstColon = strpos($line, ':');
                if($index === 0) {
                    $key = 'Http-Code';
                    $value = explode(' ', $line)[1];
                } else {
                    $key = substr($line, 0, $firstColon);
                    $value = substr($line, $firstColon + 2);
                }
                $filteredHeaders[$key] = $value;
            }
            $headers[] = $filteredHeaders;
        }
        return $headers;
    }

    protected function setResponseParameters(array $responseParameters)
    {
        return $responseParameters;
    }
}
