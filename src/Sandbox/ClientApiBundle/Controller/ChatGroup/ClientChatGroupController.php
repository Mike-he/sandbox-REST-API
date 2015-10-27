<?php

namespace Sandbox\ClientApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Rs\Json\Patch;

/**
 * Client Chat Group Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientChatGroupController extends ChatGroupController
{
    /**
     * Create a chat group.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postChatGroupAction(
        Request $request
    ) {
    }

    /**
     * List my chat groups.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupsAction(
        Request $request
    ) {
    }

    /**
     * Retrieve a given chat group.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/chatgroups/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupAction(
        Request $request,
        $id
    ) {
    }

    /**
     * Modify a given chat group.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/chatgroups/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchChatGroupAction(
        Request $request,
        $id
    ) {
    }

    /**
     * Remove / quit a given chat group.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/chatgroups/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteChatGroupAction(
        Request $request,
        $id
    ) {
    }

    /**
     * Mute a chat group.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups/{id}/mute")
     * @Method({"POST"})
     *
     * @return View
     */
    public function muteChatGroupAction(
        Request $request
    ) {
    }

    /**
     * Unmute a chat group.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/chatgroups/{id}/mute")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function unmuteChatGroupAction(
        Request $request,
        $id
    ) {
    }
}
