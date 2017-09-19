<?php
namespace HttpClient\Middleware;

use HttpClient\Middleware\MiddlewareInterface;

abstract class MiddlewareAbstract implements MiddlewareInterface
{
    const PARAMETER_ENCODE_REQUEST = 'encodeRequest';
    const PARAMETER_ROOT_NODE = 'rootNode';

    const DEFAULT_ROOT_NODE = 'Request';

    protected $next;
    protected $options;

    public function __construct(MiddlewareInterface $next = null, array $options = [])
    {
        $this->next = $next;
        $this->options = $options;
    }

    abstract public function process(array $curlOptionsArray) : array;
}
