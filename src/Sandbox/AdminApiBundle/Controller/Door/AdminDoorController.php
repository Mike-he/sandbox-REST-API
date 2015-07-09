<?php

namespace Sandbox\AdminApiBundle\Controller\Door;

use Sandbox\ApiBundle\Controller\Door\DoorController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;

/**
 * Admin Door Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminDoorController extends DoorController
{
    /**
     * @Get("/doors/session")
     *
     * @return View
     */
    public function loginAction()
    {
        $sessionXML = $this->getSessionId();
        //$session = $this->get('jms_serializer')->deserialize($sessionXML, 'ArrayCollection', 'xml');
        //$array = json_decode(json_encode((array) $xml), 1);
        //json_decode($ch, true);
        var_dump($sessionXML);
        exit;
    }
}
