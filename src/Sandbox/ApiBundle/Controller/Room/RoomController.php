<?php

namespace Sandbox\ApiBundle\Controller\Room;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Room Controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class RoomController extends SandboxRestController
{
    /**
     * Post rule ids to CRM API.
     *
     * @param Integer $roomId
     * @param Array   $ids
     * @param String  $type
     */
    protected function postPriceRule(
        $roomId,
        $ids,
        $type
    ) {
        // get auth
        $headers = apache_request_headers();
        $auth = $headers['Authorization'];

        $globals = $this->container->get('twig')->getGlobals();

        $typeUrl = null;

        switch ($type) {
            case 'include':
                $typeUrl = $globals['crm_api_admin_price_rule_include'];
                break;
            case 'exclude':
                $typeUrl = $globals['crm_api_admin_price_rule_exclude'];
                break;
            default:
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].$typeUrl;
        $apiUrl = preg_replace('/{roomId}.*?/', "$roomId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $this->get('curl_util')->callAPI(
            $ch,
            'POST',
            $auth,
            json_encode($ids)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != self::HTTP_STATUS_OK_NO_CONTENT) {
            //remove the created room
            $room = $this->getRepo('Room\Room')->find($roomId);
            $em = $this->getDoctrine()->getManager();
            $em->remove($room);
            $em->flush();

            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
    }
}
