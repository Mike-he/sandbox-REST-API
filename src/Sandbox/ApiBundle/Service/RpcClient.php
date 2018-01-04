<?php

namespace Sandbox\ApiBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class RpcClient
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $server
     * @param $method
     * @param array $params
     *
     * @return mixed
     */
    public function callRpcServer(
        $server,
        $method,
        $params = []
    ) {
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'RPC-Token: ClientTokenExample',
        ];

        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data['method'] = $method;

        if (!empty($params)) {
            $paramsJson = $this->container->get('serializer')->serialize($params, 'json');
            $params = json_decode($paramsJson, true);
            $data['params'] = $params;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);

        return json_decode($result, true);
    }
}
