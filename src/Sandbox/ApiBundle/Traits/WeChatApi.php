<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\WeChatConstants;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * TODO use a Symfony OAuth bundle instead
 * URL: https://github.com/hwi/HWIOAuthBundle.
 */
trait WeChatApi
{
    use CommonMethod;

    /**
     * @param string $code
     *
     * @return array
     */
    public function getWeChatAuthInfoByCode(
        $code
    ) {
        try {
            $globals = $this->getContainer()
                            ->get('twig')
                            ->getGlobals();

            $appId = $globals['wechat_app_id'];
            $secret = $globals['wechat_app_secret'];

            $url = WeChatConstants::URL_ACCESS_TOKEN;
            $params = "appid=$appId&secret=$secret&code=$code";
            $params = $params.'&grant_type=authorization_code';
            $apiUrl = $url.$params;

            $ch = curl_init($apiUrl);
            $response = $this->getContainer()->get('curl_util')->callAPI($ch, 'GET');
            $result = json_decode($response, true);

            if (is_null($result)
                || array_key_exists('errcode', $result)) {
                throw new UnauthorizedHttpException('WeChat login unauthorized!');
            }

            return $result;
        } catch (\Exception $e) {
            throw new UnauthorizedHttpException('WeChat login failed!');
        }
    }

    /**
     * @param WeChat $weChat
     *
     * @return array
     */
    public function doWeChatAuthByOpenIdAccessToken(
        $weChat
    ) {
        try {
            $openId = $weChat->getOpenId();
            $accessToken = $weChat->getAccessToken();

            $url = WeChatConstants::URL_AUTH;
            $params = "access_token=$accessToken&openid=$openId";
            $apiUrl = $url.$params;

            $ch = curl_init($apiUrl);
            $response = $this->getContainer()->get('curl_util')->callAPI($ch, 'GET');
            $result = json_decode($response, true);

            $errCode = $result['errcode'];
            if ($errCode != 0) {
                throw new UnauthorizedHttpException('WeChat login unauthorized!');
            }

            return $result;
        } catch (\Exception $e) {
            throw new UnauthorizedHttpException('WeChat login failed!');
        }
    }
}
