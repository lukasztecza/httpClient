<?php
namespace RestClient\Exception;

use RestClient\Exception\BaseException;

class WrongConfigurationException extends BaseException
{
    public function __construct($message = 'Worng configuration exception', $code = 500) {
        $this->message = $message;
        $this->code = $code;
    }
}
