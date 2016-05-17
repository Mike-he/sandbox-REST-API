<?php

namespace Sandbox\ApiBundle\Controller\ThirdParty;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\WeChatConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
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

        if (is_null($data) || empty($data)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get app access token
        $accessToken = $this->generateAccessToken();

        if (is_null($accessToken)) {
            return new View();
        }

        // get jsapi_ticket
        $ticket = $this->generateJsApiTicket($accessToken);

        if (is_null($ticket)) {
            return new View();
        }

        $data['jsapi_ticket'] = $ticket;

        // generate signature
        $string1 = $this->generateSignature($data);

        return new View(array(
            'signature' => sha1($string1),
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
     * @return string
     */
    private function generateAccessToken()
    {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $url = WeChatConstants::URL_APP_ACCESS_TOKEN;
        $appId = $globals['wechat_app_id'];
        $secret = $globals['wechat_app_secret'];

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
    private function generateJsApiTicket(
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

        return $string1;
    }
}
