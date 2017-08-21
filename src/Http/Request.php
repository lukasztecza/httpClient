<?php
namespace RestClient\Http;

class Request
{
    private $host;    //host some-api.com
    private $resource;//default '/' but maybe users/123
    private $query;   //query string
    private $headers; //string of headers
    private $body;    //json body or xml
    private $method;  //PUT POST GET DELETE
}
