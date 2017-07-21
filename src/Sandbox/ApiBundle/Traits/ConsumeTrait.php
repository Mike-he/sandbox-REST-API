<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\BundleConstants;

/**
 * Consume Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait ConsumeTrait
{
    use ProductOrderNotification;

    /**
     * @param int    $userId
     * @param string $amount
     * @param string $tradeId
     *
     * @return string|null
     */
    protected function postConsumeBalance(
        $userId,
        $amount,
        $tradeId,
        $invoiced = true
    ) {
        $json = $this->createJsonForConsume(
            $tradeId,
            $amount,
            $invoiced
        );
        $auth = $this->authAuthMd5($json);

        $globals = $this->getContainer()->get('twig')->getGlobals();

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_consume'];
        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'POST',
            array(BundleConstants::SANDBOX_CUSTOM_HEADER.$auth),
            $json);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != BundleConstants::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        return $result['consume_amount'];
    }

    /**
     * @return mixed
     */
    protected function createJsonForConsume(
        $orderNumber,
        $amount,
        $invoiced
    ) {
        $content = [
            'amount' => $amount,
            'trade_id' => $orderNumber,
            'invoiced' => $invoiced,
        ];

        return json_encode($content);
    }

    /**
     * @param string $json
     *
     * @return mixed
     */
    protected function authAuthMd5(
        $json
    ) {
        $globals = $this->getContainer()->get('twig')->getGlobals();

        $key = $globals['sandbox_auth_key'];

        $contentMd5 = md5($json.$key);
        $contentMd5 = strtoupper($contentMd5);

        return $contentMd5;
    }
}
