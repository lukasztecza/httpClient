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
include '../src/Middleware/SomeMiddleware.php';

use RestClient\ClientFactory;

$client_configuration = [
/*    'clientloc' => [
        'uri' => 'htpp://localhost',
        'middlewaresArray' => [['middlewareClass' => 'RestClient\Middleware\JsonMiddleware', 'middlewareOptions' => ['onlyResponse' => true]]],
        'class' => 'RestClient\Client\JsonClient',
        'timeout' => 10,
        'connectionTimeout' => 15
    ],*/
    'clientluk' => [
        'uri' => 'http://lukasztecza.pl',
        'class' => 'RestClient\Client\DefaultClient',
        'options' => [
            'connectionTimeout' => 20,
            'timeout' => 10,
            'blah' => 'wtf option mine'
        ],
        'middlewares' => [
            ['class' => 'RestClient\Middleware\SomeMiddleware']
        ]
    ]
];

$client_factory = new ClientFactory($client_configuration);

//echo 'RestClient\Client\XmlClient';
//var_dump($client_factory->getClient('test_client'), $client_factory->getClient('some_client'));exit;
//echo PHP_EOL . 'done' . PHP_EOL;

//@TODO create xml middleware
var_dump($client_factory->getClient('clientluk')->post(
    ['resource1' => 5, 'resource2' => 3],
    ['query1' => 'test1', 'query2' => 34],
    ['header1' => 'thats my header', 'header2' => 'send it there'],
    ['payload1' => 'hey you', 'payload2' => 'yupii']
));exit;

//var_dump(in_array('Throwable', class_implements(RestClient\Exception\BaseException::class)));

//echo PHP_EOL . 'done' . PHP_EOL;
//var_dump($client_factory);
