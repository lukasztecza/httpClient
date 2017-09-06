<?php
namespace RestClient;

use RestClient\Client\ClientInterface;
use RestClient\Middleware\MiddlewareInterface;
use RestClient\Middleware\CurlMiddleware;
use RestClient\Exception\WrongConfigurationException;
use RestClient\Exception\NonExistingClientConfigurationException;

class ClientFactory
{
    const PARAMETER_URI = 'uri';
    const PARAMETER_CLASS = 'class';
    const PARAMETER_OPTIONS = 'options';
    const PARAMETER_MIDDLEWARES = 'middlewares';
    const PARAMETER_CONNECTION_TIMEOUT = 'connectionTimeout';
    const PARAMETER_TIMEOUT = 'timeout';

    const DEFAULT_CLIENT_CLASS = 'RestClient\Client\DefaultClient';
    const CURL_MIDDLEWARE_CLASS = 'RestClient\Middleware\CurlMiddleware';
    const DEFAULT_CONNECTION_TIMEOUT = 5;
    const DEFAULT_TIMEOUT = 5;
    const CLIENT_OBJECT = 'client';

    private $clients;

    public function __construct(array $clientsConfiguration)
    {
        foreach ($clientsConfiguration as $clientName => $configuration) {
            if (
                !is_array($configuration) ||
                !isset($configuration[static::PARAMETER_URI]) ||
                !is_string($configuration[static::PARAMETER_URI])
            ) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, ' . static::PARAMETER_URI . ' not specified for client: ' . $clientName
                );
            }
            $this->clients[$clientName] = [static::PARAMETER_URI => $configuration[static::PARAMETER_URI]];

            if (
                isset($configuration[static::PARAMETER_CLASS])
            ) {
                if (
                    !is_string($configuration[static::PARAMETER_CLASS]) ||
                    !class_exists($configuration[static::PARAMETER_CLASS]) ||
                    !in_array(ClientInterface::class, class_implements($configuration[static::PARAMETER_CLASS]))
                ) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' . var_export($configuration[static::PARAMETER_CLASS], true) .
                        ' does not exist or does not implement ' . ClientInterface::class
                    );
                }
                $this->clients[$clientName][static::PARAMETER_CLASS] = $configuration[static::PARAMETER_CLASS];
            } else {
                $this->clients[$clientName][static::PARAMETER_CLASS] = static::DEFAULT_CLIENT_CLASS;
            }

            if (
                isset($configuration[static::PARAMETER_MIDDLEWARES]) &&
                is_array($configuration[static::PARAMETER_MIDDLEWARES])
            ) {
                foreach ($configuration[static::PARAMETER_MIDDLEWARES] as &$middlewareEntry) {
                    if (!isset($middlewareEntry[static::PARAMETER_CLASS])) {
                        throw new WrongConfigurationException(
                            'Wrong configuration exception, '  . var_export($middlewareEntry, true) .
                            ' parameter ' . static::PARAMETER_CLASS . ' is not specified in ' . static::PARAMETER_MIDDLEWARES
                        );
                    }

                    if (
                        !is_string($middlewareEntry[static::PARAMETER_CLASS]) ||
                        !class_exists($middlewareEntry[static::PARAMETER_CLASS]) ||
                        !in_array(MiddlewareInterface::class, class_implements($middlewareEntry[static::PARAMETER_CLASS]))
                    ) {
                        throw new WrongConfigurationException(
                            'Wrong configuration exception, '  . var_export($middlewareEntry, true) .
                            ' middleware class does not exist or does not implement ' . MiddlewareInterface::class
                        );
                    }

                    if (
                        isset($middlewareEntry[static::PARAMETER_OPTIONS]) &&
                        !is_array($middlewareEntry[static::PARAMETER_OPTIONS])
                    ) {
                        throw new WrongConfigurationException(
                            'Wrong configuration exception, '  . var_export($middlewareEntry[static::PARAMETER_OPTIONS], true) .
                            ' middleware options is set but is not array '
                        );
                    }

                    if (!isset($middlewareEntry[static::PARAMETER_OPTIONS])) {
                        $middlewareEntry[static::PARAMETER_OPTIONS] = [];
                    }
                }
                $this->clients[$clientName][static::PARAMETER_MIDDLEWARES] = $configuration[static::PARAMETER_MIDDLEWARES];
            }
            $this->clients[$clientName][static::PARAMETER_MIDDLEWARES][] = [
                static::PARAMETER_CLASS => static::CURL_MIDDLEWARE_CLASS,
                static::PARAMETER_OPTIONS => []
            ];

            if (isset($configuration[static::PARAMETER_OPTIONS])) {
                if (!is_array($configuration[static::PARAMETER_OPTIONS])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, '  . var_export($configuration[static::PARAMETER_OPTIONS], true) .
                        ' client options is set but is not array '
                    );
                }
            }

            if (
                isset($configuration[static::PARAMETER_OPTIONS][static::PARAMETER_CONNECTION_TIMEOUT])
            ) {
                if (!is_int($configuration[static::PARAMETER_OPTIONS][static::PARAMETER_CONNECTION_TIMEOUT])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' .
                        var_export($configuration[static::PARAMETER_OPTIONS][static::PARAMETER_CONNECTION_TIMEOUT]) .
                        ' is not int'
                    );
                }
                $this->clients[$clientName][static::PARAMETER_OPTIONS][static::PARAMETER_CONNECTION_TIMEOUT] = $configuration[static::PARAMETER_OPTIONS][
                    static::PARAMETER_CONNECTION_TIMEOUT
                ];
            } else {
                $this->clients[$clientName][static::PARAMETER_OPTIONS][static::PARAMETER_CONNECTION_TIMEOUT] = static::DEFAULT_CONNECTION_TIMEOUT;
            }

            if (
                isset($configuration[static::PARAMETER_OPTIONS][static::PARAMETER_TIMEOUT])
            ) {
                if (!is_int($configuration[static::PARAMETER_OPTIONS][static::PARAMETER_TIMEOUT])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' .
                        var_export($configuration[static::PARAMETER_OPTIONS][static::PARAMETER_TIMEOUT]) .
                        ' is not int'
                    );
                }
                $this->clients[$clientName][static::PARAMETER_OPTIONS][static::PARAMETER_TIMEOUT] = $configuration[static::PARAMETER_OPTIONS][
                    static::PARAMETER_TIMEOUT
                ];
            } else {
                $this->clients[$clientName][static::PARAMETER_OPTIONS][static::PARAMETER_TIMEOUT] = static::DEFAULT_TIMEOUT;
            }
        }
    }

    public function getClient(string $clientName) : ClientInterface
    {
        if (!isset($this->clients[$clientName])) {
            throw new NonExistingClientConfigurationException('Client configuration does not exists for client: ' . $clientName);
        }

        if (!isset($this->clients[$clientName][static::CLIENT_OBJECT])) {
            $currentMiddleware = null;
            $middlewaresArrayLength = count($this->clients[$clientName][static::PARAMETER_MIDDLEWARES]);
            while ($middlewaresArrayLength--) {
                $options = $this->clients[$clientName][static::PARAMETER_MIDDLEWARES][$middlewaresArrayLength][static::PARAMETER_OPTIONS];
                $middleware = new $this->clients[$clientName][static::PARAMETER_MIDDLEWARES][$middlewaresArrayLength][static::PARAMETER_CLASS](
                    $currentMiddleware,
                    $options
                );
                $currentMiddleware = $middleware;
            }

            $this->clients[$clientName][static::CLIENT_OBJECT] = new $this->clients[$clientName][static::PARAMETER_CLASS](
                $this->clients[$clientName][static::PARAMETER_URI],
                $currentMiddleware,
                $this->clients[$clientName][static::PARAMETER_OPTIONS][static::PARAMETER_CONNECTION_TIMEOUT],
                $this->clients[$clientName][static::PARAMETER_OPTIONS][static::PARAMETER_TIMEOUT]
            );
        }

        return $this->clients[$clientName][static::CLIENT_OBJECT];
    }
}
