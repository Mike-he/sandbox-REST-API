<?php

namespace Sandbox\ApiBundle\Traits;

trait CurlUtil
{
    /**
     * @param $ch
     * @param $method
     * @param $headers
     * @param $data
     *
     * @return mixed
     */
    public function callAPI(
        $ch,
        $method,
        $headers = null,
        $data = null
    ) {
        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ('PUT' === $method || 'DELETE' === $method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if (is_null($headers)) {
            $headers = array();
        }
        $headers[] = 'Accept: application/json';

        if (!is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return curl_exec($ch);
    }

    /**
     * Call api without get response.
     *
     * @param $ch
     * @param $method
     * @param $headers
     * @param $data
     *
     * @return mixed
     */
    public function asyncCallAPI(
        $ch,
        $method,
        $headers = null,
        $data = null
    ) {
        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ('PUT' === $method || 'DELETE' === $method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if (is_null($headers)) {
            $headers = array();
        }
        $headers[] = 'Accept: application/json';

        if (!is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // without response
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return curl_exec($ch);
    }
}
