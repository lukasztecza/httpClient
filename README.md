# httpClient
Http client using curl, based on middleware and factory patterns.

### Basic usage
- assuming that your app is `myRepo/myApp` then include using composer:
```
{
    "name": "myRepo/myApp",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/lukasztecza/tinyAppBase"
       }
    ],
    "require": {
        "lukasztecza/tinyAppBase": "dev-master"
    },
    "autoload": {
        "psr-4": { "MyApp\\": "src/" }
    }
}
```
- create factory object and pass it configuration array with structure:
```php
new HttpClient\ClientFactory;

$clientFactory = new ClientFactory([
    'firstMinimalClient' => [
        'url' => 'http://www.mysite.com'
    ],
    'secondClientWithLotsOfConfiguration' => [
        'url' => 'http://www.myothersite.com',
        'class' => 'MyNamespace\MyCustomClientClassWhichExtendsClientAbstract',
        'options' => [
            'connectionTimeout' => 10,
            'timeout' => 10,
            'myOptionAccessibleInMyCustomClient' => 'someOptionForClient'
        ],
        'middlewares' => [
            [
                'class' => 'HttpClient\Middleware\XmlMiddleware',
                'options' => [
                    'encodeRequest' => false,
                    'rootNode' => 'SomeApiRelatedRootXmlNode'
                ]
            ],
            [
                'class' => 'MyNamespace\MyCustomMiddlewareClassWhichExtendsMiddlewareAbstract',
                'options' => [
                    'myOptionAccessibleInMyMiddleware' => 'someOptionForMiddleware'
                ]            
            ]
        ]
    ]
]);
```
- get client using factory
```
$firstMinimalClient = $clientFactory->getClient('firstMinimalClient');
//will send simple get request
$firstMinimalClient->get();


$resource = [
    'customers' => null,
    'page' => 3
];
$query = [
    'date-from' => '2017-09-01',
    'date-to' => '2017-09-11'
];
$headers = [
    'Authentication' => 'Bearer 123123'
];
$payload = [
    'name' => 'John'
];
//will build request with all passed data, note that get() and delete() will not pass payload
//post(), put(), patch() will pass the payload
$firstMinimalClient->post($resource, $query, $headers, $payload);
```
