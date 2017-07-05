<?php

namespace jean553\OpenfireBundle\Logic;

use GuzzleHttp\Client;

class OpenfireClient
{
    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * @var array
     *
     * Configuration with url and secret
     */
    private $config;

    /**
     * @param array $config 'url' and 'secret'
     */
    public function __construct($config)
    {
        $this->client = new Client();
        $this->config = $config;
    }

    /**
     * Execute a request according to the given type, url and parameters.
     *
     * @param string $type post, get, put, delete
     * @param string $action endpoint to call
     * @param array $params request parameters
     */
    public function request(
        $type, 
        $action, 
        $params
    ) {
        $headers = array(
            'Accept' => 'application/json',
            'Authorization' => $this->config['secret']
        );

        $body = json_encode($params);

        // TODO: similar logic for each method, should be refactored
        switch($type)
        {
            case 'get':

                $result = 
                    $this->client->get(
                        $this->config['url'].$action,
                        array(
                            'headers' => $headers
                        ) 
                    );
                break;
            case 'post':
                $headers += ['Content-Type' => 'application/json'];

                $result = 
                    $this->client->post(
                        $this->config['url'].$action,
                        array(
                            'headers' => $headers,
                            'body' => $body
                        ) 
                    );
                break;
            case 'put':
                $headers += ['Content-Type' => 'application/json'];

                $result = 
                    $this->client->put(
                        $this->config['url'].$action,
                        array(
                            'headers' => $headers,
                            'body' => $body
                        ) 
                    );
                break;
            case 'delete':
                $headers += ['Content-Type' => 'application/json'];

                $result = 
                    $this->client->delete(
                        $this->config['url'].$action,
                        array(
                            'headers' => $headers,
                            'body' => $body
                        ) 
                    );
                break;
        }

        return $result->json();
    }
}
