<?php
namespace RestClient\Exception;

use RestClient\Exception\BaseException;

class WrongConfigurationException extends BaseException
{
    public function __construct(string $message = 'Worng configuration exception', int $code = 500) {
        $this->message = $message;
        $this->code = $code;
    }
}
