<?php

namespace Sandbox\SalesApiBundle\Controller\ChatGroup;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Chat Group Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminChatGroupController extends ChatGroupController
{
    /**
     * List my chat groups.
     *
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by name, phone and email"
     * )
     *
     * @Route("/chatgroups")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'id' => $userId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $adminPlatform = $this->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];
        $search = $paramFetcher->get('search');

        // get my chat groups
        $chatGroups = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->getAdminChatGroups(
                $companyId,
                $userId,
                $search
            );

        // response
        return new View($chatGroups);
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
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'id' => $userId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $adminPlatform = $this->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        // get chat group
        $chatGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->getAdminChatGroupById(
                $id,
                $companyId,
                $userId
            );
        if (is_null($chatGroup)) {
            return new View();
        }

        $members = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
            ->getChatGroupMembersByGroup($chatGroup);

        $chatGroup->setMembers($members);

        return new View($chatGroup);
    }
}
