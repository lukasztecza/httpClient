<?php
namespace RestClient\Exception;

use RestClient\Exception\ExceptionInterface;

class BadRequestException extends \Exception implements ExceptionInterface
{
    private $message;
    private $code;

    public function __construct($message, $code) {
        $this->message = $message;
        $this->code = $code;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getCode() {
        return $this->code;
    }
}
