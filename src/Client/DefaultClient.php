<?php
namespace RestClient\Client;

use RestClient\Client\ClientAbstract;

class DefaultClient extends ClientAbstract
{
    protected function getClientCurlOptions() : array
    {
        return [
            CURLOPT_HEADER => true
        ];
    }

    protected function getClientResource() : array
    {
        return [
            'resource3' => null
        ];
    }

    protected function getClientQuery() : array
    {
        return [
            'query3' => 'haha'
        ];
    }

    protected function getClientHeaders() : array
    {
        return [
            'Authentication' => 'Bearer 123321' . $this->options['blah']
        ];
    }

    protected function getClientPayload() : array
    {
        return [
            'payload3' => 'hey'
        ];
    }
}
