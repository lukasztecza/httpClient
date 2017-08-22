<?php
namespace RestClient\Exception;

use RestClient\Exception\ExceptionInterface;

class BaseException extends \Exception implements ExceptionInterface
{
    protected $message;
    protected $code;

    public function __construct(string $message, int $code) {
        $this->message = $message;
        $this->code = $code;
    }
}
