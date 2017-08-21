<?php
namespace RestClient\Exception;

interface ExceptionInterface
{
    public function __construct(string $message, int $code);
    public function getMessage();
    public function getCode();
}
