<?php
namespace RestClient\Exception;

use RestClient\Exception\BaseException;

class NonExistingClientConfigurationException extends BaseException
{
    public function __construct(string $message = 'Non exsisting client configuration exception', int $code = 500) {
        $this->message = $message;
        $this->code = $code;
    }
}
