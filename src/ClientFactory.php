<?php
namespace RestClient;

use RestClient\Exception\WrongConfigurationException;

class ClientFactory
{
    private $client_configuration;

    public function __construct(array $client_configuration) {
        foreach ($client_configuration as $client_name => $parameters) {
            if (
                !array_key_exists('uri', $parameters) ||
                !array_key_exists('timeout', $parameters) ||
                !array_key_exists('type', $parameters)
            ) {
                throw new \WrongConfigurationException(
                    'Wrong configuration exception, uri or timeout or type not specified for client: ' . $client_name, 500
                );
            }
        }
        $this->client_configuration;
    }

    public function getClient(string $client) {
        return $this->client_configuration;
        //@TODO return new Client object according to type and set uri to uri for it
    }
}
