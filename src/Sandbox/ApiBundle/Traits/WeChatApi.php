<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Constants\WeChatConstants;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * TODO use a Symfony OAuth bundle instead
 * URL: https://github.com/hwi/HWIOAuthBundle.
 */
trait WeChatApi
{
    use CommonMethod;
    use StringUtil;
    use CurlUtil;

    /**
     * @param string $code
     * @param string $from
     *
     * @return array
     */
    public function getWeChatAuthInfoByCode(
        $code,
        $from,
        $platform
    ) {
        $code = $this->after('_', $code);

        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

        if ($platform == PlatformConstants::PLATFORM_COMMNUE) {
            // get appid by data from type
            if (ThirdPartyOAuthWeChatData::DATA_FROM_APPLICATION == $from) {
                $appId = $globals['wechat_commnue_app_id'];
                $secret = $globals['wechat_commnue_secret'];
            } elseif (ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE == $from) {
                $appId = $globals['wechat_website_app_id'];
                $secret = $globals['wechat_website_secret'];
            }
        } else {
            // get appid by data from type
            if (ThirdPartyOAuthWeChatData::DATA_FROM_APPLICATION == $from) {
                $appId = $globals['wechat_app_id'];
                $secret = $globals['wechat_app_secret'];
            } elseif (ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE == $from) {
                $appId = $globals['wechat_website_app_id'];
                $secret = $globals['wechat_website_secret'];
            }
        }

        $url = WeChatConstants::URL_ACCESS_TOKEN;
        $params = "appid=$appId&secret=$secret&code=$code";
        $params = $params.'&grant_type=authorization_code';
        $apiUrl = $url.$params;

        $ch = curl_init($apiUrl);
        $response = $this->callAPI($ch, 'GET');
        $result = json_decode($response, true);

        if (is_null($result)
            || array_key_exists('errcode', $result)) {
            throw new UnauthorizedHttpException('WeChat login unauthorized!');
        }

        return $result;
    }

    /**
     * @param WeChat $weChat
     *
     * @return array
     */
    public function throwUnauthorizedIfWeChatAuthFail(
        $weChat
    ) {
        $openId = $weChat->getOpenId();
        $accessToken = $weChat->getAccessToken();

        $url = WeChatConstants::URL_AUTH;
        $params = "access_token=$accessToken&openid=$openId";
        $apiUrl = $url.$params;

        $ch = curl_init($apiUrl);
        $response = $this->callAPI($ch, 'GET');
        $result = json_decode($response, true);

        $errCode = $result['errcode'];
        if (0 != $errCode) {
            throw new UnauthorizedHttpException('WeChat login unauthorized!');
        }

        return $result;
    }

    /**
     * @param WeChat $weChat
     *
     * @return array
     */
    public function getWeChatSnsUserInfo(
        $weChat
    ) {
        $openId = $weChat->getOpenId();
        $accessToken = $weChat->getAccessToken();

        $url = WeChatConstants::URL_USER_INFO;
        $params = "access_token=$accessToken&openid=$openId";
        $apiUrl = $url.$params;

        $ch = curl_init($apiUrl);
        $response = $this->callAPI($ch, 'GET');
        $result = json_decode($response, true);

        return $result;
    }
}
