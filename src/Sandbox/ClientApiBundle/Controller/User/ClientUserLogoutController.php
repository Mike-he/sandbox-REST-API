<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserLogoutController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;

/**
 * Logout controller.
 *
 * @category Sandbox
 *
 * @author   Albert Feng
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientUserLogoutController extends UserLogoutController
{
    /**
     * Logout.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "NO CONTENT"
     *  }
     * )
     *
     * @Route("/logout")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postClientUserLogoutAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $clientId = $this->getUser()->getClientId();

        // delete user tokens of this client
        $this->getRepo('User\UserToken')->deleteUserToken(
            $userId,
            $clientId
        );

        // disable APNS in XMPP
        $requestContent = $request->getContent();
        if (!is_null($requestContent)) {
            $this->disableApnsInXmpp(json_decode($requestContent, true));
        }

        return new View();
    }

    /**
     * @param $payload
     */
    private function disableApnsInXmpp(
        $payload
    ) {
        if (is_null($payload)
            || !array_key_exists('apns', $payload)) {
            return;
        }

        try {
            $apnsData = $payload['apns'];
            if (is_null($apnsData)) {
                return;
            }

            $token = $apnsData['token'];
            if (is_null($token)) {
                return;
            }

            // request json
            $jsonDataArray = array(
                'token' => $token,
                'enabled' => false,
                'keepalive' => false,
            );
            $jsonData = json_encode($jsonDataArray);

            // call openfire APNS api
            $this->callOpenfireApnsApi($jsonData);
        } catch (\Exception $e) {
            error_log('Disable APNS in XMPP went wrong!');
        }
    }

    /**
     * @param object $jsonData
     *
     * @return mixed|void
     */
    protected function callOpenfireApnsApi(
        $jsonData
    ) {
        try {
            // get globals
            $globals = $this->getGlobals();

            // openfire API URL
            $apiURL = $globals['openfire_innet_url'].
                $globals['openfire_plugin_bstios'].
                $globals['openfire_plugin_bstios_apns'];

            // init curl
            $ch = curl_init($apiURL);

            // get then response when post OpenFire API
            $response = $this->get('curl_util')->callAPI($ch, 'POST', null, $jsonData);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != self::HTTP_STATUS_OK) {
                return;
            }

            return $response;
        } catch (\Exception $e) {
            error_log('Call Openfire APNS API went wrong!');
        }
    }
}
