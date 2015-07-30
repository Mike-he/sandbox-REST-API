<?php

/**
 * Created by PhpStorm.
 * User: josh
 * Date: 5/5/15
 * Time: 3:11 PM.
 */
namespace Sandbox\ApiBundle\Utils;

class CurlUtil
{
    /**
     * @param $ch
     * @param $data
     * @param $auth
     * @param $method
     *
     * @return mixed
     */
    public function callAPI(
        $ch,
        $method,
        $auth,
        $data = null
    ) {
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $headers = array('Accept: application/json');
        $headers[] = 'Authorization: '.$auth;

        if (!is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return curl_exec($ch);
    }
}
