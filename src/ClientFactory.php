<?php
namespace RestClient;

use RestClient\Client\ClientInterface;
use RestClient\Middleware\MiddlewareInterface;
use RestClient\Middleware\CurlMiddleware;
use RestClient\Exception\WrongConfigurationException;
use RestClient\Exception\NonExistingClientConfigurationException;

class ClientFactory
{
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
            !isset($configuration[ClientInterface::PARAMETER_URI]) ||
            !is_string($configuration[ClientInterface::PARAMETER_URI])
        ) {
            throw new WrongConfigurationException(
                'Wrong configuration exception, ' . ClientInterface::PARAMETER_URI . ' not specified for client: ' . $clientName
            );
        }
        $this->clients[$clientName] = [ClientInterface::PARAMETER_URI => $configuration[ClientInterface::PARAMETER_URI]];
    }

    private function configureClass(string $clientName, array $configuration)
    {
        if (isset($configuration[ClientInterface::PARAMETER_CLASS])) {
            if (
                !is_string($configuration[ClientInterface::PARAMETER_CLASS]) ||
                !class_exists($configuration[ClientInterface::PARAMETER_CLASS]) ||
                !in_array(ClientInterface::class, class_implements($configuration[ClientInterface::PARAMETER_CLASS]))
            ) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, ' . var_export($configuration[ClientInterface::PARAMETER_CLASS], true) .
                    ' class does not exist or does not implement ' . ClientInterface::class
                );
            }
            $this->clients[$clientName][ClientInterface::PARAMETER_CLASS] = $configuration[ClientInterface::PARAMETER_CLASS];
        } else {
            $this->clients[$clientName][ClientInterface::PARAMETER_CLASS] = ClientInterface::DEFAULT_CLIENT_CLASS;
        }
    }

    private function configureMiddlewares(string $clientName, array $configuration)
    {
        if (isset($configuration[ClientInterface::PARAMETER_MIDDLEWARES])) {
            if (!is_array($configuration[ClientInterface::PARAMETER_MIDDLEWARES])) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, parameter '  . ClientInterface::PARAMETER_MIDDLEWARES .
                    ' is set but is not array'
                );
            }

            foreach ($configuration[ClientInterface::PARAMETER_MIDDLEWARES] as &$middlewareEntry) {
                if (!isset($middlewareEntry[ClientInterface::PARAMETER_CLASS])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, '  . var_export($middlewareEntry, true) .
                        ' parameter ' . ClientInterface::PARAMETER_CLASS . ' is not specified in ' . ClientInterface::PARAMETER_MIDDLEWARES
                    );
                }

                if (
                    !is_string($middlewareEntry[ClientInterface::PARAMETER_CLASS]) ||
                    !class_exists($middlewareEntry[ClientInterface::PARAMETER_CLASS]) ||
                    !in_array(MiddlewareInterface::class, class_implements($middlewareEntry[ClientInterface::PARAMETER_CLASS]))
                ) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, '  . var_export($middlewareEntry, true) .
                        ' middleware class does not exist or does not implement ' . MiddlewareInterface::class
                    );
                }

                $this->configureMiddlewareOptions($middlewareEntry);
            }

            $this->clients[$clientName][ClientInterface::PARAMETER_MIDDLEWARES] = $configuration[ClientInterface::PARAMETER_MIDDLEWARES];
        }

        $this->clients[$clientName][ClientInterface::PARAMETER_MIDDLEWARES][] = [
            ClientInterface::PARAMETER_CLASS => ClientInterface::CURL_MIDDLEWARE_CLASS,
            ClientInterface::PARAMETER_OPTIONS => []
        ];
    }

    private function configureMiddlewareOptions(&$middlewareEntry)
    {
        if (isset($middlewareEntry[ClientInterface::PARAMETER_OPTIONS])) {
            if (!is_array($middlewareEntry[ClientInterface::PARAMETER_OPTIONS])) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, '  . var_export($middlewareEntry[ClientInterface::PARAMETER_OPTIONS], true) .
                    ' middleware options is set but is not array '
                );
            }

            if (
                $middlewareEntry[ClientInterface::PARAMETER_CLASS] === ClientInterface::JSON_MIDDLEWARE_CLASS ||
                $middlewareEntry[ClientInterface::PARAMETER_CLASS] === ClientInterface::XML_MIDDLEWARE_CLASS
            ) {
                if (isset($middlewareEntry[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_ENCODE_REQUEST])) {
                    if (!is_bool($middlewareEntry[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_ENCODE_REQUEST])) {
                        throw new WrongConfigurationException(
                            'Wrong configuration exception, '  . var_export($middlewareEntry[ClientInterface::PARAMETER_OPTIONS], true) .
                            ' middleware option ' . ClientInterface::PARAMETER_ENCODE_REQUEST . ' is set but is not boolean'
                        );
                    }
                } else {
                    $middlewareEntry[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_ENCODE_REQUEST] = true;
                }
            }

            if ($middlewareEntry[ClientInterface::PARAMETER_CLASS] === ClientInterface::XML_MIDDLEWARE_CLASS) {
                if (isset($middlewareEntry[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_ROOT_NODE])) {
                    if (!is_string($middlewareEntry[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_ROOT_NODE])) {
                        throw new WrongConfigurationException(
                            'Wrong configuration exception, '  . var_export($middlewareEntry[ClientInterface::PARAMETER_OPTIONS], true) .
                            ' middleware option ' . ClientInterface::PARAMETER_ROOT_NODE . ' is set but is not string'
                        );
                    }
                } else {
                    $middlewareEntry[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_ROOT_NODE] = 'Request';
                }
            }
        } else {
            $middlewareEntry[ClientInterface::PARAMETER_OPTIONS] = [];

            if (
                $middlewareEntry[ClientInterface::PARAMETER_CLASS] === ClientInterface::JSON_MIDDLEWARE_CLASS ||
                $middlewareEntry[ClientInterface::PARAMETER_CLASS] === ClientInterface::XML_MIDDLEWARE_CLASS
            ) {
                $middlewareEntry[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_ENCODE_REQUEST] = true;
            }

            if ($middlewareEntry[ClientInterface::PARAMETER_CLASS] === ClientInterface::XML_MIDDLEWARE_CLASS) {
                $middlewareEntry[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_ROOT_NODE] = 'Request';
            }
        }
    }

    private function configureClientOptions(string $clientName, array $configuration)
    {
        if (
            isset($configuration[ClientInterface::PARAMETER_OPTIONS]) &&
            !is_array($configuration[ClientInterface::PARAMETER_OPTIONS])
        ) {
            throw new WrongConfigurationException(
                'Wrong configuration exception, '  . var_export($configuration[ClientInterface::PARAMETER_OPTIONS], true) .
                ' client options is set but is not array'
            );
        }

        if (
            isset($configuration[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_CONNECTION_TIMEOUT])
        ) {
            if (!is_int($configuration[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_CONNECTION_TIMEOUT])) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, ' .
                    var_export($configuration[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_CONNECTION_TIMEOUT]) .
                    ' is not int'
                );
            }
        } else {
            $configuration[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_CONNECTION_TIMEOUT] = ClientInterface::DEFAULT_CONNECTION_TIMEOUT;
        }

        if (isset($configuration[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_TIMEOUT])) {
            if (!is_int($configuration[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_TIMEOUT])) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, ' .
                    var_export($configuration[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_TIMEOUT]) .
                    ' is not int'
                );
            }
        } else {
            $configuration[ClientInterface::PARAMETER_OPTIONS][ClientInterface::PARAMETER_TIMEOUT] = ClientInterface::DEFAULT_TIMEOUT;
        }
        $this->clients[$clientName][ClientInterface::PARAMETER_OPTIONS] = $configuration[ClientInterface::PARAMETER_OPTIONS];
    }

    public function getClient(string $clientName) : ClientInterface
    {
        if (!isset($this->clients[$clientName])) {
            throw new NonExistingClientConfigurationException('Client configuration does not exists for client: ' . $clientName);
        }

        if (!isset($this->clients[$clientName][ClientInterface::CLIENT_OBJECT])) {
            $currentMiddleware = null;
            $middlewaresArrayLength = count($this->clients[$clientName][ClientInterface::PARAMETER_MIDDLEWARES]);
            while ($middlewaresArrayLength--) {
                $options = $this->clients[$clientName][ClientInterface::PARAMETER_MIDDLEWARES][$middlewaresArrayLength][ClientInterface::PARAMETER_OPTIONS];
                $middleware = new $this->clients[$clientName][ClientInterface::PARAMETER_MIDDLEWARES][$middlewaresArrayLength][ClientInterface::PARAMETER_CLASS](
                    $currentMiddleware,
                    $options
                );
                $currentMiddleware = $middleware;
            }

            $this->clients[$clientName][ClientInterface::CLIENT_OBJECT] = new $this->clients[$clientName][ClientInterface::PARAMETER_CLASS](
                $this->clients[$clientName][ClientInterface::PARAMETER_URI],
                $currentMiddleware,
                $this->clients[$clientName][ClientInterface::PARAMETER_OPTIONS]
            );
        }

        return $this->clients[$clientName][ClientInterface::CLIENT_OBJECT];
    }
}
