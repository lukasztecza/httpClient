<?php
namespace RestClient\Client;

use RestClient\Http\Request;

interface ClientInterface
{
    const PARAMETER_URI = 'uri';
    const PARAMETER_TIMEOUT = 'timeout';
    const PARAMETER_TYPE = 'type';

    const TYPE_DEFAULT = 'default';
    const TYPE_JSON = 'json';

    public function get(Request $request);
    public function post(Request $request);
    public function put(Request $request);
    public function patch(Request $request);
    public function delete(Request $request);
}
