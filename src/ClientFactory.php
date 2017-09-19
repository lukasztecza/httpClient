<?php
namespace HttpClient;

use HttpClient\Client\ClientInterface;
use HttpClient\Client\ClientAbstract;
use HttpClient\Middleware\MiddlewareInterface;
use HttpClient\Middleware\MiddlewareAbstract;
use HttpClient\Exception\WrongConfigurationException;
use HttpClient\Exception\NonExistingClientConfigurationException;

class ClientFactory
{
    const PARAMETER_URI = 'uri';
    const PARAMETER_CLASS = 'class';
    const PARAMETER_MIDDLEWARES = 'middlewares';
    const PARAMETER_OPTIONS = 'options';

    const DEFAULT_CLIENT_CLASS = 'HttpClient\Client\DefaultClient';
    const CURL_MIDDLEWARE_CLASS = 'HttpClient\Middleware\CurlMiddleware';
    const JSON_MIDDLEWARE_CLASS = 'HttpClient\Middleware\JsonMiddleware';
    const XML_MIDDLEWARE_CLASS = 'HttpClient\Middleware\XmlMiddleware';
    const CLIENT_OBJECT = 'client';

    private $clients;

    public function __construct(array $clientsConfiguration)
    {
        foreach ($clientsConfiguration as $clientName => $configuration) {
            $this->configureUri($clientName, $configuration);
            $this->configureClass($clientName, $configuration);
            $this->configureMiddlewares($clientName, $configuration);
            $this->configureClientOptions($clientName, $configuration);
        }
    }

    private function configureUri(string $clientName, array $configuration)
    {
        if (
            !is_array($configuration) ||
            !isset($configuration[self::PARAMETER_URI]) ||
            !is_string($configuration[self::PARAMETER_URI])
        ) {
            throw new WrongConfigurationException(
                'Wrong configuration exception, ' . self::PARAMETER_URI . ' not specified for client ' . $clientName
            );
        }
        $this->clients[$clientName] = [self::PARAMETER_URI => $configuration[self::PARAMETER_URI]];
    }

    private function configureClass(string $clientName, array $configuration)
    {
        if (isset($configuration[self::PARAMETER_CLASS])) {
            if (
                !is_string($configuration[self::PARAMETER_CLASS]) ||
                !class_exists($configuration[self::PARAMETER_CLASS]) ||
                !in_array(ClientInterface::class, class_implements($configuration[self::PARAMETER_CLASS]))
            ) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, ' . var_export($configuration[self::PARAMETER_CLASS], true) .
                    ' class does not exist or does not implement ' . ClientInterface::class
                );
            }
            $this->clients[$clientName][self::PARAMETER_CLASS] = $configuration[self::PARAMETER_CLASS];
        } else {
            $this->clients[$clientName][self::PARAMETER_CLASS] = self::DEFAULT_CLIENT_CLASS;
        }
    }

    private function configureMiddlewares(string $clientName, array $configuration)
    {
        if (isset($configuration[self::PARAMETER_MIDDLEWARES])) {
            if (!is_array($configuration[self::PARAMETER_MIDDLEWARES])) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, parameter '  . self::PARAMETER_MIDDLEWARES .
                    ' is set but is not array'
                );
            }

            foreach ($configuration[self::PARAMETER_MIDDLEWARES] as &$middlewareEntry) {
                if (!isset($middlewareEntry[self::PARAMETER_CLASS])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, '  . var_export($middlewareEntry, true) .
                        ' parameter ' . self::PARAMETER_CLASS .
                        ' is not specified in ' . self::PARAMETER_MIDDLEWARES . ' array'
                    );
                }

                if (
                    !is_string($middlewareEntry[self::PARAMETER_CLASS]) ||
                    !class_exists($middlewareEntry[self::PARAMETER_CLASS]) ||
                    !in_array(MiddlewareInterface::class, class_implements($middlewareEntry[self::PARAMETER_CLASS]))
                ) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, '  . var_export($middlewareEntry, true) .
                        ' middleware class does not exist or does not implement ' . MiddlewareInterface::class
                    );
                }

                $this->configureMiddlewareOptions($middlewareEntry);
            }

            $this->clients[$clientName][self::PARAMETER_MIDDLEWARES] = $configuration[self::PARAMETER_MIDDLEWARES];
        }

        $this->clients[$clientName][self::PARAMETER_MIDDLEWARES][] = [
            self::PARAMETER_CLASS => self::CURL_MIDDLEWARE_CLASS,
            self::PARAMETER_OPTIONS => []
        ];
    }

    private function configureMiddlewareOptions(&$middlewareEntry)
    {
        if (isset($middlewareEntry[self::PARAMETER_OPTIONS])) {
            if (!is_array($middlewareEntry[self::PARAMETER_OPTIONS])) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, '  . var_export($middlewareEntry[self::PARAMETER_OPTIONS], true) .
                    ' middleware options is set but is not array '
                );
            }

            if (
                $middlewareEntry[self::PARAMETER_CLASS] === self::JSON_MIDDLEWARE_CLASS ||
                $middlewareEntry[self::PARAMETER_CLASS] === self::XML_MIDDLEWARE_CLASS
            ) {
                if (isset($middlewareEntry[self::PARAMETER_OPTIONS][MiddlewareAbstract::PARAMETER_ENCODE_REQUEST])) {
                    if (!is_bool($middlewareEntry[self::PARAMETER_OPTIONS][MiddlewareAbstract::PARAMETER_ENCODE_REQUEST])) {
                        throw new WrongConfigurationException(
                            'Wrong configuration exception, '  . var_export($middlewareEntry[self::PARAMETER_OPTIONS], true) .
                            ' middleware option ' . MiddlewareAbstract::PARAMETER_ENCODE_REQUEST . ' is set but is not boolean'
                        );
                    }
                } else {
                    $middlewareEntry[self::PARAMETER_OPTIONS][MiddlewareAbstract::PARAMETER_ENCODE_REQUEST] = true;
                }
            }

            if ($middlewareEntry[self::PARAMETER_CLASS] === self::XML_MIDDLEWARE_CLASS) {
                if (isset($middlewareEntry[self::PARAMETER_OPTIONS][MiddlewareAbstract::PARAMETER_ROOT_NODE])) {
                    if (!is_string($middlewareEntry[self::PARAMETER_OPTIONS][MiddlewareAbstract::PARAMETER_ROOT_NODE])) {
                        throw new WrongConfigurationException(
                            'Wrong configuration exception, '  . var_export($middlewareEntry[self::PARAMETER_OPTIONS], true) .
                            ' middleware option ' . MiddlewareAbstract::PARAMETER_ROOT_NODE . ' is set but is not string'
                        );
                    }
                } else {
                    $middlewareEntry[self::PARAMETER_OPTIONS][MiddlewareAbstract::PARAMETER_ROOT_NODE] = MiddlewareAbstract::DEFAULT_ROOT_NODE;
                }
            }
        } else {
            $middlewareEntry[self::PARAMETER_OPTIONS] = [];

            if (
                $middlewareEntry[self::PARAMETER_CLASS] === self::JSON_MIDDLEWARE_CLASS ||
                $middlewareEntry[self::PARAMETER_CLASS] === self::XML_MIDDLEWARE_CLASS
            ) {
                $middlewareEntry[self::PARAMETER_OPTIONS][MiddlewareAbstract::PARAMETER_ENCODE_REQUEST] = true;
            }

            if ($middlewareEntry[self::PARAMETER_CLASS] === self::XML_MIDDLEWARE_CLASS) {
                $middlewareEntry[self::PARAMETER_OPTIONS][MiddlewareAbstract::PARAMETER_ROOT_NODE] = MiddlewareAbstract::DEFAULT_ROOT_NODE;
            }
        }
    }

    private function configureClientOptions(string $clientName, array $configuration)
    {
        if (
            isset($configuration[self::PARAMETER_OPTIONS]) &&
            !is_array($configuration[self::PARAMETER_OPTIONS])
        ) {
            throw new WrongConfigurationException(
                'Wrong configuration exception, '  . var_export($configuration[self::PARAMETER_OPTIONS], true) .
                ' client options is set but is not array'
            );
        }

        if (
            isset($configuration[self::PARAMETER_OPTIONS][ClientAbstract::PARAMETER_CONNECTION_TIMEOUT])
        ) {
            if (!is_int($configuration[self::PARAMETER_OPTIONS][ClientAbstract::PARAMETER_CONNECTION_TIMEOUT])) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, ' .
                    var_export($configuration[self::PARAMETER_OPTIONS][ClientAbstract::PARAMETER_CONNECTION_TIMEOUT]) .
                    ' is set but is not integer'
                );
            }
        } else {
            $configuration[self::PARAMETER_OPTIONS][ClientAbstract::PARAMETER_CONNECTION_TIMEOUT] = ClientAbstract::DEFAULT_CONNECTION_TIMEOUT;
        }

        if (isset($configuration[self::PARAMETER_OPTIONS][ClientAbstract::PARAMETER_TIMEOUT])) {
            if (!is_int($configuration[self::PARAMETER_OPTIONS][ClientAbstract::PARAMETER_TIMEOUT])) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, ' .
                    var_export($configuration[self::PARAMETER_OPTIONS][ClientAbstract::PARAMETER_TIMEOUT]) .
                    ' is set but is not integer'
                );
            }
        } else {
            $configuration[self::PARAMETER_OPTIONS][ClientAbstract::PARAMETER_TIMEOUT] = ClientAbstract::DEFAULT_TIMEOUT;
        }
        $this->clients[$clientName][self::PARAMETER_OPTIONS] = $configuration[self::PARAMETER_OPTIONS];
    }

    public function getClient(string $clientName) : ClientInterface
    {
        if (!isset($this->clients[$clientName])) {
            throw new NonExistingClientConfigurationException('Client configuration does not exists for client ' . $clientName);
        }

        if (!isset($this->clients[$clientName][self::CLIENT_OBJECT])) {
            $currentMiddleware = null;
            $middlewaresArrayLength = count($this->clients[$clientName][self::PARAMETER_MIDDLEWARES]);
            while ($middlewaresArrayLength--) {
                $options = $this->clients[$clientName][self::PARAMETER_MIDDLEWARES][$middlewaresArrayLength][self::PARAMETER_OPTIONS];
                $middleware = new $this->clients[$clientName][self::PARAMETER_MIDDLEWARES][$middlewaresArrayLength][self::PARAMETER_CLASS](
                    $currentMiddleware,
                    $options
                );
                $currentMiddleware = $middleware;
            }

            $this->clients[$clientName][self::CLIENT_OBJECT] = new $this->clients[$clientName][self::PARAMETER_CLASS](
                $this->clients[$clientName][self::PARAMETER_URI],
                $currentMiddleware,
                $this->clients[$clientName][self::PARAMETER_OPTIONS]
            );
        }

        return $this->clients[$clientName][self::CLIENT_OBJECT];
    }
}
