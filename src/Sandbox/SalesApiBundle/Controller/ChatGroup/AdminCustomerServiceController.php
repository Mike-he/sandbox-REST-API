<?php

namespace Sandbox\SalesApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServiceMember;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminCustomerServiceController extends ChatGroupController
{
    /**
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="tag",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by tag"
     * )
     *
     * @Route("/customerservice/members")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServiceMembersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $tag = $paramFetcher->get('tag');

        $serviceMembers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findBy(array(
                'tag' => $tag,
                'companyId' => $companyId,
            ));

        return new View($serviceMembers);
    }

    /**
     * Update CustomerService members.
     *
     * @param Request $request the request object
     *
     * @Route("/customerservice/members")
     * @Method({"POST"})
     *
     * @return View
     */
    public function updateMemberAction(
        Request $request
    ) {
        $em = $this->getDoctrine()->getManager();

        $adminPlatform = $this->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $content = json_decode($request->getContent(), true);

        if (!isset($content['tag'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $tag = $content['tag'];

        $addGroups = [];
        $removeGroups = [];

        $chatGroups = $em->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->findBy(array(
                'tag' => $tag,
                'companyId' => $companyId,
            ));

        if (isset($content['add'])) {
            $addMembers = $content['add'];

            foreach ($addMembers as $addMember) {
                $userId = $addMember['user_id'];

                $serviceMember = $em->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
                    ->findOneBy(array(
                        'tag' => $tag,
                        'userId' => $userId,
                        'companyId' => $companyId,
                    ));

                if ($serviceMember) {
                    continue;
                }

                $member = new RoomBuildingServiceMember();
                $member->setCompanyId($companyId);
                $member->setTag($tag);
                $member->setUserId($userId);
                $em->persist($member);

                //Add chatGroup member
                foreach ($chatGroups as $chatGroup) {
                    $groupMember = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
                        ->findOneBy([
                            'chatGroup' => $chatGroup,
                            'user' => $userId,
                        ]);
                    if (!is_null($groupMember)) {
                        continue;
                    }

                    $newGroupMember = new ChatGroupMember();
                    $newGroupMember->setChatGroup($chatGroup);
                    $newGroupMember->setUser($userId);
                    $newGroupMember->setAddBy($this->getUser()->getMyUser());

                    $em->persist($newGroupMember);

                    $groupId = $chatGroup->getId();
                    $addGroups[$groupId][] = $userId;
                }
            }
        }

        if (isset($content['remove'])) {
            $removeMembers = $content['remove'];

            foreach ($removeMembers as $removeMember) {
                $userId = $removeMember['user_id'];

                $serviceMember = $em->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
                    ->findOneBy(array(
                        'tag' => $tag,
                        'userId' => $userId,
                        'companyId' => $companyId,
                    ));

                if ($serviceMember) {
                    $em->remove($serviceMember);

                    //Remove chatGroup member
                    foreach ($chatGroups as $chatGroup) {
                        $groupMember = $this->getDoctrine()
                            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
                            ->findOneBy([
                                'chatGroup' => $chatGroup,
                                'user' => $userId,
                            ]);

                        if (is_null($groupMember)) {
                            continue;
                        }

                        $em->remove($groupMember);

                        $groupId = $chatGroup->getId();
                        $removeGroups[$groupId][] = $userId;
                    }
                }
            }
        }

        $em->flush();

        $appKey = $this->getParameter('jpush_property_key');
        foreach ($addGroups as $key => $users) {
            $group = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
                ->find($key);

            $membersIds = [];
            foreach ($users as $userId) {
                $salesAdmin = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                    ->findOneBy(array('userId' => $userId));
                if ($salesAdmin) {
                    $membersIds[] = $salesAdmin->getXmppUsername();
                }
            }
            // call openfire
            $this->addXmppChatGroupMember($group, $membersIds, $appKey);
        }

        foreach ($removeGroups as $key => $users) {
            $group = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
                ->find($key);

            $membersIds = [];
            foreach ($users as $userId) {
                $salesAdmin = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                    ->findOneBy(array('userId' => $userId));
                if ($salesAdmin) {
                    $membersIds[] = $salesAdmin->getXmppUsername();
                }
            }

            // call openfire
            $this->deleteXmppChatGroupMember($group, $membersIds, $appKey);
        }

        $view = new View();

        return $view;
    }
}
