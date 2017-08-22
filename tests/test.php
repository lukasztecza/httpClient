<?php

//@TODO here utilize phpunit
include '../src/Client/ClientInterface.php';
include '../src/ClientFactory.php';
include '../src/Exception/ExceptionInterface.php';
include '../src/Exception/BaseException.php';
include '../src/Exception/WrongConfigurationException.php';

use RestClient\ClientFactory;

$client_configuration = [
    'test_client' => ['uri' => 'localhost', 'type' => 'json', 'timeout' => 10],
//    'some_client' => ['type' => 'json', 'timeout' => 10],
];

$client_factory = new ClientFactory($client_configuration);

//echo 'RestClient\Client\XmlClient';

//echo PHP_EOL . 'done' . PHP_EOL;

var_dump(in_array('Throwable1', class_implements(RestClient\Exception\BaseException::class)));

//echo PHP_EOL . 'done' . PHP_EOL;
//var_dump($client_factory);
