<?php
namespace HttpClient\Middleware;

use HttpClient\Middleware\MiddlewareAbstract;

class CurlMiddleware extends MiddlewareAbstract
{
    public function process(array $curlOptionsArray) : array
    {
        $curlSession = curl_init();
        curl_setopt_array($curlSession, $curlOptionsArray);
        $rawResponse = curl_exec($curlSession);
        $info = curl_getinfo($curlSession);
        curl_close($curlSession);

        $responseHeaderSize = $info['header_size'];
        $responseHeader = substr($rawResponse, 0, $responseHeaderSize);
        $headers = $this->getHeadersFromResponse($responseHeader);
        $body = substr($rawResponse, $responseHeaderSize);
        return ['info' => $info, 'headers' => $headers, 'body' => $body];
    }

    private function getHeadersFromResponse(string $responseHeader) : array
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
                    $value = $line;
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
}
