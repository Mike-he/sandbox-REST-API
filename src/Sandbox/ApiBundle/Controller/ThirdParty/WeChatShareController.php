<?php

namespace Sandbox\ApiBundle\Controller\ThirdParty;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\WeChatConstants;
use Sandbox\ApiBundle\Controller\User\UserLoginController;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChatShares;
use Sandbox\ApiBundle\Traits\CurlUtil;
use Sandbox\ApiBundle\Form\ThirdParty\ThirdPartyWeChatShareType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WeChatShareController extends UserLoginController
{
    use CurlUtil;

    const KEY_ACCESS_TOKEN = 'access_token';
    const KEY_JSAPI_TICKET = 'jsapi_ticket';

    const INVALID_ACCESS_TOKEN_CODE = 400001;
    const INVALID_ACCESS_TOKEN_MESSAGE = 'Invalid access token';
    const INVALID_JSPAI_TICKET_CODE = 400002;
    const INVALID_JSAPI_TICKET_MESSAGE = 'Invalid jspai ticket';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/wechat/share/signature")
     * @Method({"POST"})
     *
     * @return View
     */
    public function getWeChatShareSignatureAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check authorization
        $this->checkAuthorization();

        // bind form
        $weChatShareInput = new WeChatShares();
        $form = $this->createForm(new ThirdPartyWeChatShareType(), $weChatShareInput);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $weChatShare = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ThirdParty\WeChatShares')
            ->findOneBy(array(
                'appId' => $weChatShareInput->getAppId(),
            ));

        if (is_null($weChatShare)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get app access token
        $now = new \DateTime('now');

        $expiresIn = $weChatShare->getExpiresIn();
        $appId = $weChatShare->getAppId();
        $expiresTime = $now->modify('-'.$expiresIn.' seconds');
        $jsapiTicket = $weChatShare->getJsapiTicket();

        // updat access token
        if ($weChatShare->getModificationDate() < $expiresTime) {
            $accessToken = $this->generateAccessTokenByCurl();

            if (is_null($accessToken)) {
                return $this->customErrorView(
                    400,
                    self::INVALID_ACCESS_TOKEN_CODE,
                    self::INVALID_ACCESS_TOKEN_MESSAGE
                );
            }

            // get jsapi_ticket
            $jsapiTicket = $this->generateJsApiTicketByCurl($accessToken);

            if (is_null($jsapiTicket)) {
                return $this->customErrorView(
                    400,
                    self::INVALID_JSPAI_TICKET_CODE,
                    self::INVALID_JSAPI_TICKET_MESSAGE
                );
            }

            $weChatShare->setAccessToken($accessToken);
            $weChatShare->setJsapiTicket($jsapiTicket);
            $weChatShare->setModificationDate(new \DateTime());
        }

        $data['url'] = $weChatShare->getUrl();
        $data['noncestr'] = $this->createNonceStr();
        $data['timestamp'] = time();
        $data['jsapi_ticket'] = $jsapiTicket;

        // generate signature
        $signature = $this->generateSignature($data);

        $em->flush();

        return new View(array(
            'appId' => $appId,
            'nonceStr' => $data['noncestr'],
            'signature' => $signature,
            'timestamp' => $data['timestamp'],
        ));
    }

    /**
     * @return string
     */
    private function generateAccessTokenByCurl()
    {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $url = WeChatConstants::URL_APP_ACCESS_TOKEN;
        $appId = $globals['wechat_public_platform_app_id'];
        $secret = $globals['wechat_public_platform_secret'];

        $apiUrl = $url.'?grant_type=client_credential'.'&appid='.$appId.'&secret='.$secret;
        $ch = curl_init($apiUrl);

        $response = $this->callAPI($ch, 'GET');

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $result = json_decode($response, true);

        if (!array_key_exists('access_token', $result)) {
            return;
        }

        return $result['access_token'];
    }

    /**
     * @param $accessToken
     *
     * @return string
     */
    private function generateJsApiTicketByCurl(
        $accessToken
    ) {
        $ticketUrl = WeChatConstants::URL_JSAPI_TICKET;

        $ticketApiUrl = $ticketUrl.'?access_token='.$accessToken.'&type=jsapi';
        $ticketCh = curl_init($ticketApiUrl);

        $ticketResponse = $this->callAPI($ticketCh, 'GET');

        $httpCode = curl_getinfo($ticketCh, CURLINFO_HTTP_CODE);
        if ($httpCode != self::HTTP_STATUS_OK) {
            return;
        }

        $ticketResult = json_decode($ticketResponse, true);

        if (!array_key_exists('ticket', $ticketResult)) {
            return;
        }

        return $ticketResult['ticket'];
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function generateSignature(
        $data
    ) {
        foreach ($data as $key => $value) {
            $keys[] = $key;
        }

        // sort the key index by ASCII
        sort($keys);

        $string1 = null;
        $first = true;
        foreach ($keys as $key) {
            if ($first) {
                $first = false;
            } else {
                $string1 .= '&';
            }

            $string1 = $string1.$key.'='.$data[$key];
        }

        return sha1($string1);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    private function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';

        for ($i = 0; $i < $length; ++$i) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }
}
