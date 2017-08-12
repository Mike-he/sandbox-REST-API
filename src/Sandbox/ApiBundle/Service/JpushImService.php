<?php

namespace Sandbox\ApiBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class JpushService.
 */
class JpushImService
{
    const REQUEST_ERROR_MESSAGE = 'Jpush im error';

    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->client = new Client();
    }

    public function createUser(
        $username,
        $password
    ) {
        try {
            $this->request(
                'post',
                '/v1/users/',
                array(
                    array(
                        'username' => $username,
                        'password' => $password,
                    )
                )

            );
        } catch (RequestException $e) {
            throw new \Exception(self::REQUEST_ERROR_MESSAGE);
        }
    }

    public function updatePassword(
        $username,
        $password
    ) {
        try {
            $this->request(
                'put',
                '/v1/users/'.$username.'/password',
                array(
                    'password' => $password,
                )

            );
        } catch (RequestException $e) {
            throw new \Exception(self::REQUEST_ERROR_MESSAGE);
        }
    }

    public function updateNickname(
        $username,
        $nickname
    ) {
        try {
            $this->request(
                'put',
                '/v1/users/'.$username,
                array(
                    'nickname' => $nickname,
                )

            );
        } catch (RequestException $e) {
            throw new \Exception(self::REQUEST_ERROR_MESSAGE);
        }
    }

    public function request(
        $type,
        $action,
        $params
    ) {
        $url = $this->container->getParameter('jpush_im_rest_url');
        $key = $this->container->getParameter('jpush_key');
        $secret = $this->container->getParameter('jpush_secret');

        $auth = base64_encode($key.':'.$secret);

        $headers = array(
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.$auth,
        );

        $body = json_encode($params);

        // TODO: similar logic for each method, should be refactored
        switch ($type) {
            case 'get':

                $result =
                    $this->client->get(
                        $url.$action,
                        array(
                            'headers' => $headers,
                        )
                    );
                break;
            case 'post':
                $headers += ['Content-Type' => 'application/json'];

                $result =
                    $this->client->post(
                        $url.$action,
                        array(
                            'headers' => $headers,
                            'body' => $body,
                        )
                    );

                break;
            case 'put':
                $headers += ['Content-Type' => 'application/json'];

                $result =
                    $this->client->put(
                        $url.$action,
                        array(
                            'headers' => $headers,
                            'body' => $body,
                        )
                    );
                break;
            case 'delete':
                $headers += ['Content-Type' => 'application/json'];

                $result =
                    $this->client->delete(
                        $url.$action,
                        array(
                            'headers' => $headers,
                            'body' => $body,
                        )
                    );
                break;
        }

        return $result->json();
    }
}
