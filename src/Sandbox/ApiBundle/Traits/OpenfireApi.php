<?php

namespace Sandbox\ApiBundle\Traits;

/**
 * Openfire API Trait.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait OpenfireApi
{
    use CommonMethod;

    /**
     * @param string $method
     * @param object $jsonData
     *
     * @return mixed|void
     */
    protected function callOpenfireApnsApi(
        $method,
        $jsonData
    ) {
        try {
            // get globals
            $globals = $this->getContainer()
                            ->get('twig')
                            ->getGlobals();

            // openfire API URL
            $apiURL = $globals['openfire_innet_url'].
                $globals['openfire_plugin_bstios'].
                $globals['openfire_plugin_bstios_apns'];

            return $this->callOpenfireApi($method, $apiURL, $jsonData);
        } catch (\Exception $e) {
            error_log('Call Openfire APNS API went wrong!');
        }
    }

    /**
     * @param string $method
     * @param string $apiURL
     * @param object $jsonData
     *
     * @return mixed|void
     */
    protected function callOpenfireApi(
        $method,
        $apiURL,
        $jsonData
    ) {
        try {
            // init curl
            $ch = curl_init($apiURL);

            // get then response when post OpenFire API
            $response = $this->getContainer()->get('curl_util')->callAPI($ch, $method, null, $jsonData);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                return;
            }

            return $response;
        } catch (\Exception $e) {
            error_log('Call Openfire API went wrong!');
        }
    }
}
