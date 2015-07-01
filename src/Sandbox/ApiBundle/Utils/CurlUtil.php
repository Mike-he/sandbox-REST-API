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
        $data,
        $auth,
        $method
    ) {
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:'.$auth));

        return curl_exec($ch);
    }
}
