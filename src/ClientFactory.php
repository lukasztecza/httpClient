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
    const PARAMETER_CLIENT_CLASS = 'clientClass';
    const PARAMETER_MIDDLEWARES_ARRAY = 'middlewaresArray';
    const PARAMETER_CONNECTION_TIMEOUT = 'connectionTimeout';
    const PARAMETER_TIMEOUT = 'timeout';
    const PARAMETER_CLIENT = 'client';

    const DEFAULT_CLIENT_CLASS = 'RestClient\Client\DefaultClient';
    const DEFAULT_CONNECTION_TIMEOUT = 5;
    const DEFAULT_TIMEOUT = 5;

    private $clientsConfiguration;
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
                isset($configuration[static::PARAMETER_CLIENT_CLASS])
            ) {
                if (
                    !is_string($configuration[static::PARAMETER_CLIENT_CLASS]) ||
                    !class_exists($configuration[static::PARAMETER_CLIENT_CLASS]) ||
                    !in_array(ClientInterface::class, class_implements($configuration[static::PARAMETER_CLIENT_CLASS]))
                ) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' . var_export($configuration[static::PARAMETER_CLIENT_CLASS], true) .
                        ' does not exist or does not implement ' . ClientInterface::class
                    );
                }
                $this->clients[$clientName][static::PARAMETER_CLIENT_CLASS] = $configuration[static::PARAMETER_CLIENT_CLASS];
            } else {
                $this->clients[$clientName][static::PARAMETER_CLIENT_CLASS] = static::DEFAULT_CLIENT_CLASS;
            }

            if (
                isset($configuration[static::PARAMETER_MIDDLEWARES_ARRAY]) &&
                is_array($configuration[static::PARAMETER_MIDDLEWARES_ARRAY])
            ) {
                foreach ($configuration[static::PARAMETER_MIDDLEWARES_ARRAY] as $middlewareClass) {
                    if (
                        !is_string($middlewareClass) ||
                        !class_exists($middlewareClass) ||
                        !in_array(MiddlewareInterface::class, class_implements($middlewareClass))
                    ) {
                        throw new WrongConfigurationException(
                            'Wrong configuration exception, '  . var_export($middlewareClass, true) .
                            ' does not exist or does not implement ' . MiddlewareInterface::class
                        );
                    }
                }
                $this->clients[$clientName][static::PARAMETER_MIDDLEWARES_ARRAY] = $configuration[static::PARAMETER_MIDDLEWARES_ARRAY];
            } else {
                $this->clients[$clientName][static::PARAMETER_MIDDLEWARES_ARRAY] = [static::CURL_MIDDLEWARE_CLASS];
            }

            if (
                isset($configuration[static::PARAMETER_CONNECTION_TIMEOUT])
            ) {
                if (!is_int($configuration[static::PARAMETER_CONNECTION_TIMEOUT])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' . var_export($configuration[static::PARAMETER_CONNECTION_TIMEOUT]) . ' is not int'
                    );
                }
                $this->clients[$clientName][static::PARAMETER_CONNECTION_TIMEOUT] = $configuration[
                    static::PARAMETER_CONNECTION_TIMEOUT
                ];
            } else {
                $this->clients[$clientName][static::PARAMETER_CONNECTION_TIMEOUT] = static::DEFAULT_CONNECTION_TIMEOUT;
            }

            if (
                isset($configuration[static::PARAMETER_TIMEOUT])
            ) {
                if (!is_int($configuration[static::PARAMETER_TIMEOUT])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' . var_export($configuration[static::PARAMETER_TIMEOUT]) . ' is not int'
                    );
                }
                $this->clients[$clientName][static::PARAMETER_TIMEOUT] = $configuration[static::PARAMETER_TIMEOUT];
            } else {
                $this->clients[$clientName][static::PARAMETER_TIMEOUT] = static::DEFAULT_TIMEOUT;
            }
        }
    }

    public function getClient(string $clientName) : ClientInterface
    {
        if (!isset($this->clients[$clientName])) {
            throw new NonExistingClientConfigurationException('Client configuration does not exists for client: ' . $clientName);
        }

        if (!isset($this->clients[$clientName][static::PARAMETER_CLIENT])) {
            $currentMiddleware = new CurlMiddleware();
            $middlewaresArrayLength = count($this->clients[$clientName][static::PARAMETER_MIDDLEWARES_ARRAY]);
            while ($middlewaresArrayLength--) {
                $middleware = new $this->clients[$clientName][static::PARAMETER_MIDDLEWARES_ARRAY][$middlewaresArrayLength]($currentMiddleware);
                $currentMiddleware = $middleware;
            }
            $this->clients[$clientName][static::PARAMETER_CLIENT] = new $this->clients[$clientName][static::PARAMETER_CLIENT_CLASS](
                $this->clients[$clientName][static::PARAMETER_URI],
                $currentMiddleware,
                $this->clients[$clientName][static::PARAMETER_CONNECTION_TIMEOUT],
                $this->clients[$clientName][static::PARAMETER_TIMEOUT]
            );
        }

        return $this->clients[$clientName][static::PARAMETER_CLIENT];
    }
}
