<?php
namespace RestClient;

use RestClient\Client\ClientInterface;
use RestClient\Client\BaseClient;
use RestClient\Client\JsonClient;
use RestClient\Exception\WrongConfigurationException;
use RestClient\Exception\NonExistingClientConfigurationException;

class ClientFactory
{
    private $clients_configuration;
    private $clients;

    public function __construct(array $clients_configuration) {
        foreach ($clients_configuration as $client_name => $configuration) {
            if (
                !is_array($configuration) ||
                !array_key_exists(ClientInterface::PARAMETER_URI, $configuration) ||
                !array_key_exists(ClientInterface::PARAMETER_TIMEOUT, $configuration) ||
                !array_key_exists(ClientInterface::PARAMETER_TYPE, $configuration)
            ) {
                throw new WrongConfigurationException(
                    'Wrong configuration exception, ' .
                    ClientInterface::PARAMETER_URI . ' or ' .
                    ClientInterface::PARAMETER_TIMEOUT . ' or ' .
                    ClientInterface::PARAMETER_TYPE . ' not specified for client: ' .
                    $client_name,
                    500
                );
            }
        }
        $this->clients_configuration = $clients_configuration;
        $this->clients = [];
    }

    public function getClient(string $client_name) {
        if (!array_key_exists($client_name, $this->clients_configuration)) {
            throw new NonExistingClientConfigurationException('Client configuration does not exists for client: ' . $client_name);
        }

        if (!isset($this->clients[$client_name])) {
            switch (true) {
                case $this->clients_configuration[$client_name]['type'] === ClientInterface::TYPE_DEFAULT:
                    $this->clients[$client_name] = new BaseClient();
                    break;
                case $this->clients_configuration[$client_name]['type'] === ClientInterface::TYPE_JSON:
                    $this->clients[$client_name] = new JsonClient();
                    break;
                case
                    class_exists($this->clients_configuration[$client_name]['type']) &&
                    in_array(ClientInterface::class, class_implements($this->clients_configuration[$client_name]['type']))
                    : 
                    $this->clients[$client_name] = new $client_name['type']();
                    break;
                default:
                    throw new WrongClientTypeException(
                        'Unsupported client type: ' . $this->clients_configuration[$client_name]['type'] . 
                        ' for client: ' . $client_name['type'],
                        500
                    );
            }
        }

        return $this->clients[$client_name];
    }
}
