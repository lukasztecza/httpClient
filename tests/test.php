<?php

//@TODO here utilize phpunit
include '../src/ClientFactory.php';
include '../src/Client/ClientInterface.php';
include '../src/Exception/ExceptionInterface.php';
include '../src/Exception/ExceptionAbstract.php';
include '../src/Exception/WrongConfigurationException.php';
include '../src/Exception/WrongResourceException.php';
include '../src/Client/ClientAbstract.php';
include '../src/Client/DefaultClient.php';
include '../src/Middleware/MiddlewareInterface.php';
include '../src/Middleware/MiddlewareAbstract.php';
include '../src/Middleware/CurlMiddleware.php';
include '../src/Middleware/JsonMiddleware.php';
include '../src/Middleware/XmlMiddleware.php';

use HttpClient\ClientFactory;

$client_configuration = [
    'clientluk' => [
        'uri' => 'http://lukasztecza.pl',
        'class' => 'HttpClient\Client\DefaultClient',
        'options' => [
            'connectionTimeout' => 20,
            'timeout' => 10,
            'blah' => 'wtf option mine'
        ],
        'middlewares' => [
//            ['class' => 'HttpClient\Middleware\JsonMiddleware'],
//            ['class' => 'HttpClient\Middleware\XmlMiddleware']
        ]
    ]
];

$client_factory = new ClientFactory($client_configuration);

var_dump($client_factory->getClient('clientluk')->post(
    ['resource1' => 5, 'resource2' => 3],
    ['query1' => 'test1', 'query2' => 34],
    ['header1' => 'thats my header', 'header2' => 'send it there'],
    ['payload1' => 'hey you', 'payload2' => 'yupii']
));exit;
