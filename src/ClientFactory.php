<?php
namespace RestClient;

use RestClient\Client\ClientInterface;
use RestClient\Exception\WrongConfigurationException;
use RestClient\Exception\NonExistingClientConfigurationException;

class ClientFactory
{
    private $clientsConfiguration;
    private $clients;

    public function __construct(array $clientsConfiguration)
    {
        foreach ($clientsConfiguration as $clientName => $configuration) {
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

            if (
                isset($configuration[ClientInterface::PARAMETER_CLIENT_CLASS])
            ) {
                if (
                    !is_string($configuration[ClientInterface::PARAMETER_CLIENT_CLASS]) ||
                    !class_exists($configuration[ClientInterface::PARAMETER_CLIENT_CLASS]) ||
                    !in_array(ClientInterface::class, class_implements($configuration[ClientInterface::PARAMETER_CLIENT_CLASS]))
                ) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' . var_export($configuration[ClientInterface::PARAMETER_CLIENT_CLASS], true) .
                        ' does not exist or does not implement ' . ClientInterface::class
                    );
                }
                $this->clients[$clientName][ClientInterface::PARAMETER_CLIENT_CLASS] = $configuration[ClientInterface::PARAMETER_CLIENT_CLASS];
            } else {
                $this->clients[$clientName][ClientInterface::PARAMETER_CLIENT_CLASS] = ClientInterface::DEFAULT_CLIENT_CLASS;
            }

            if (
                isset($configuration[ClientInterface::PARAMETER_MIDDLEWARES_ARRAY]) &&
                is_array($configuration[ClientInterface::PARAMETER_MIDDLEWARES_ARRAY])
            ) {
                foreach ($configuration[ClientInterface::PARAMETER_MIDDLEWARES_ARRAY] as $middlewareClass) {
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
                $this->clients[$clientName][ClientInterface::PARAMETER_MIDDLEWARES_ARRAY] = $configuration[ClientInterface::PARAMETER_MIDDLEWARES_ARRAY];
            } else {
                $this->clients[$clientName][ClientInterface::PARAMETER_MIDDLEWARES_ARRAY] = [];
            }

            if (
                isset($configuration[ClientInterface::PARAMETER_CONNECTION_TIMEOUT])
            ) {
                if (!is_int($configuration[ClientInterface::PARAMETER_CONNECTION_TIMEOUT])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' . var_export($configuration[ClientInterface::PARAMETER_CONNECTION_TIMEOUT]) . ' is not int'
                    );
                }
                $this->clients[$clientName][ClientInterface::PARAMETER_CONNECTION_TIMEOUT] = $configuration[
                    ClientInterface::PARAMETER_CONNECTION_TIMEOUT
                ];
            } else {
                $this->clients[$clientName][ClientInterface::PARAMETER_CONNECTION_TIMEOUT] = ClientInterface::DEFAULT_CONNECTION_TIMEOUT;
            }

            if (
                isset($configuration[ClientInterface::PARAMETER_TIMEOUT])
            ) {
                if (!is_int($configuration[ClientInterface::PARAMETER_TIMEOUT])) {
                    throw new WrongConfigurationException(
                        'Wrong configuration exception, ' . var_export($configuration[ClientInterface::PARAMETER_TIMEOUT]) . ' is not int'
                    );
                }
                $this->clients[$clientName][ClientInterface::PARAMETER_TIMEOUT] = $configuration[ClientInterface::PARAMETER_TIMEOUT];
            } else {
                $this->clients[$clientName][ClientInterface::PARAMETER_TIMEOUT] = ClientInterface::DEFAULT_TIMEOUT;
            }
        }
    }

    public function getClient(string $clientName) : ClientInterface
    {
        if (!isset($this->clients[$clientName])) {
            throw new NonExistingClientConfigurationException('Client configuration does not exists for client: ' . $clientName);
        }

        if (!isset($this->clients[$clientName][ClientInterface::PARAMETER_CLIENT])) {
            $this->clients[$clientName][ClientInterface::PARAMETER_CLIENT] = new $this->clients[$clientName][ClientInterface::PARAMETER_CLIENT_CLASS](
                $this->clients[$clientName][ClientInterface::PARAMETER_URI],
                $this->clients[$clientName][ClientInterface::PARAMETER_MIDDLEWARES_ARRAY],
                $this->clients[$clientName][ClientInterface::PARAMETER_CONNECTION_TIMEOUT],
                $this->clients[$clientName][ClientInterface::PARAMETER_TIMEOUT]
            );
        }
        return $this->clients[$clientName][ClientInterface::PARAMETER_CLIENT];
    }
}
