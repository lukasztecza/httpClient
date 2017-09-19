<?php
namespace HttpClient\Client;

use HttpClient\Client\ClientAbstract;

class DefaultClient extends ClientAbstract
{
    protected function getClientCurlOptions() : array
    {
        return [];
    }

    protected function getClientResource() : array
    {
        return [];
    }

    protected function getClientQuery() : array
    {
        return [];
    }

    protected function getClientHeaders() : array
    {
        return [];
    }

    protected function getClientPayload() : array
    {
        return [];
    }
}
