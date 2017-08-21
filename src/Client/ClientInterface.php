<?php
namespace RestClient;

use RestClient\Http\Request;

interface ClientInterface
{
    public function get(Request $request);
    public function post(Request $request);
    public function put(Request $request);
    public function patch(Request $request);
    public function delete(Request $request);
}
