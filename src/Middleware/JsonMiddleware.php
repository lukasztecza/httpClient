<?php
namespace HttpClient\Middleware;

use HttpClient\Middleware\MiddlewareAbstract;

class JsonMiddleware extends MiddlewareAbstract
{
    public function process(array $curlOptionsArray) : array
    {
        if ($this->options[self::PARAMETER_ENCODE_REQUEST]) {
            $curlOptionsArray[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            if (isset($curlOptionsArray[CURLOPT_POSTFIELDS])) {
                $curlOptionsArray[CURLOPT_POSTFIELDS] = json_encode($curlOptionsArray[CURLOPT_POSTFIELDS]);
            }
        }

        $response = $this->next->process($curlOptionsArray);
        $response['body'] = json_decode($response['body'], true);
        return $response;
    }
}
