<?php

namespace Sandbox\ApiBundle\Traits;

trait StringUtil
{
    /**
     * @param $length
     *
     * @return null|string
     */
    public function randomKeys($length)
    {
        $key = null;

        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';

        for ($i = 0; $i < $length; ++$i) {
            $key .= $pattern[mt_rand(0, 35)];
        }

        return $key;
    }

    /**
     * @return string
     */
    public function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * @param $tag
     * @param $inthat
     *
     * @return string
     */
    public function after(
        $tag,
        $inthat
    ) {
        return substr($inthat, strpos($inthat, $tag) + strlen($tag));
    }

    /**
     * @param $tag
     * @param $inthat
     *
     * @return string
     */
    public function before(
        $tag,
        $inthat
    ) {
        return substr($inthat, 0, strpos($inthat, $tag));
    }

    /**
     * @param $begin
     * @param $end
     * @param $inthat
     *
     * @return mixed
     */
    public function between(
        $begin,
        $end,
        $inthat
    ) {
        return $this->before($end, $this->after($begin, $inthat));
    }

    /**
     * @param string $basicAuth
     *
     * @return string
     */
    public function getUsernameFromBasicAuth(
        $basicAuth
    ) {
        return $this->before(':', $this->getDecodedBasicAuth($basicAuth));
    }

    /**
     * @param string $basicAuth
     *
     * @return string
     */
    public function getPasswordFromBasicAuth(
        $basicAuth
    ) {
        return $this->after(':', $this->getDecodedBasicAuth($basicAuth));
    }

    /**
     * @param string $basicAuth
     *
     * @return string
     */
    public function getDecodedBasicAuth(
        $basicAuth
    ) {
        return base64_decode($this->after('Basic ', $basicAuth));
    }
}
