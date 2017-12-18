<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

class ClientChatGroupMemberController extends ChatGroupController
{
    /**
     * Get members.
     *
     * @param Request $request contains request info
     * @param int     $gid     id of the company
     *
     * @Route("/chatgroups/{gid}/members")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupMembersAction(
        Request $request,
        $gid
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        // get chat group and members
        $chatGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->findOneBy(array(
                    'gid' => $gid,
                    'companyId' => $salesCompanyId,
                ));
        $this->throwNotFoundIfNull($chatGroup, self::NOT_FOUND_MESSAGE);

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'userId' => $chatGroup->getCreatorId(),
                'companyId' => $salesCompanyId,
            ));

        $members = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
            ->getChatGroupMembers($chatGroup);

        $admins = [];
        foreach ($members as $member) {
            /** @var ChatGroupMember $member */
            $admin = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                ->findOneBy(array('userId' => $member->getUser()));

            $companyProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy(array(
                    'userId' => $member->getUser(),
                    'salesCompanyId' => $salesCompanyId,
                ));

            if (!$companyProfile) {
                $companyProfile = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy(array(
                        'userId' => $member->getUser(),
                        'salesCompanyId' => null,
                    ));
            }

            $admins[] = array(
                'xmpp_username' => $admin->getXmppUsername(),
                'name' => $companyProfile->getNickname(),
                'avatar' => $companyProfile->getAvatar(),
            );
        }

        $membersArray = array(
            'customer' => array(
                'id' => $customer ? $customer->getId() : '',
                'name' => $customer ? $customer->getName() : '',
                'avatar' => $customer ? $customer->getAvatar() : '',
                'phone' => $customer ? $customer->getPhone() : '',
            ),
            'admin' => $admins,
        );

        // set view
        $view = new View($membersArray);

        return $view;
    }
}
