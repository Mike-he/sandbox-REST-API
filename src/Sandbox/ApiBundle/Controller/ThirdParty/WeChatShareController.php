<?php

namespace Sandbox\ApiBundle\Controller\ThirdParty;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\WeChatConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChatShare;
use Sandbox\ApiBundle\Traits\CurlUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class WeChatShareController extends SandboxRestController
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

        $data = json_decode($request->getContent(), true);

        if (is_null($data) || empty($data) || !array_key_exists('url', $data)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');
        $expiresTime = $now->modify('-2 hours');

        // get app access token
        $accessToken = $this->getAccessToken(
            $em,
            $expiresTime
        );

        if (is_null($accessToken)) {
            return $this->customErrorView(
                400,
                self::INVALID_ACCESS_TOKEN_CODE,
                self::INVALID_ACCESS_TOKEN_MESSAGE
            );
        }

        // get jsapi_ticket
        $ticket = $this->getJsApiTicket(
            $em,
            $expiresTime,
            $accessToken
        );

        if (is_null($ticket)) {
            return $this->customErrorView(
                400,
                self::INVALID_JSPAI_TICKET_CODE,
                self::INVALID_JSAPI_TICKET_MESSAGE
            );
        }

        $data['noncestr'] = $this->createNonceStr();
        $data['timestamp'] = time();
        $data['jsapi_ticket'] = $ticket;

        // generate signature
        $signature = $this->generateSignature($data);

        $em->flush();

        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $appId = $globals['wechat_public_platform_app_id'];

        return new View(array(
            'app_id' => $appId,
            'nonceStr' => $data['noncestr'],
            'signature' => $signature,
            'timestamp' => $data['timestamp'],
        ));
    }

    /**
     * Check authorization.
     */
    private function checkAuthorization()
    {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $authKey = $globals['sandbox_auth_key'];

        $headerKey = self::SANDBOX_CLIENT_LOGIN_HEADER;

        $headers = apache_request_headers();

        if (!array_key_exists($headerKey, $headers)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        $auth = $headers[$headerKey];

        if ($auth != md5($authKey)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        return;
    }

    /**
     * @param $em
     * @param $expiresTime
     *
     * @return string
     */
    private function getAccessToken(
        $em,
        $expiresTime
    ) {
        $accessToken = $this->getRepo('ThirdParty\WeChatShare')->findOneByKeyName(self::KEY_ACCESS_TOKEN);

        if (is_null($accessToken)) {
            $accessToken = new WeChatShare();

            $accessToken->setKeyName(self::KEY_ACCESS_TOKEN);
            $accessToken->setValue($this->generateAccessTokenByCurl());

            $em->persist($accessToken);
        }

        if ($accessToken->getModificationDate() < $expiresTime) {
            $accessToken->setValue($this->generateAccessTokenByCurl());
            $accessToken->setModificationDate(new \DateTime());
        }

        return $accessToken->getValue();
    }

    /**
     * @param $em
     * @param $expiresTime
     * @param $accessToken
     *
     * @return string
     */
    private function getJsApiTicket(
        $em,
        $expiresTime,
        $accessToken
    ) {
        $ticket = $this->getRepo('ThirdParty\WeChatShare')->findOneByKeyName(self::KEY_JSAPI_TICKET);

        if (is_null($ticket)) {
            $ticket = new WeChatShare();

            $ticket->setKeyName(self::KEY_JSAPI_TICKET);
            $ticket->setValue($this->generateJsApiTicketByCurl($accessToken));

            $em->persist($ticket);
        }

        if ($ticket->getModificationDate() < $expiresTime) {
            $ticket->setValue($this->generateJsApiTicketByCurl($accessToken));
            $ticket->setModificationDate(new \DateTime());
        }

        return $ticket->getValue();
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
