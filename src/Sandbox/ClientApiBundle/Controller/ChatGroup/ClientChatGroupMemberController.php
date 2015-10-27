<?php

namespace Sandbox\ClientApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Client Chat Group Member Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientChatGroupMemberController extends ChatGroupController
{
    /**
     * List all chat group members.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups/{id}/members")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupMembersAction(
        Request $request
    ) {
    }
}
