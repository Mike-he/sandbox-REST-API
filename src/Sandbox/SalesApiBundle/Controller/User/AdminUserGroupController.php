<?php

namespace Sandbox\SalesApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Entity\User\UserGroup;
use Sandbox\ApiBundle\Entity\User\UserGroupHasUser;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;

class AdminUserGroupController extends SalesRestController
{
    /**
     * Get user groups.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Route("/user/groups")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserGroupsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $userGroups = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroup')
            ->findBy(array('companyId' => $salesCompanyId));

        foreach ($userGroups as $userGroup) {
            if ($userGroup->getType() == UserGroup::TYPE_CARD) {
                $buildingIds = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\UserGroupDoors')
                    ->getBuildingIdsByGroup($userGroup);

                $buildings = array();
                foreach ($buildingIds as $buildingId) {
                    $building = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                        ->find($buildingId['building']);
                    $buildings[] = array(
                        'id' => $buildingId['building'],
                        'name' => $building ? $building->getName() : null,
                    );
                }

                $userGroup->setBuilding($buildings);
            }

            $userCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->countUserNumber($userGroup);

            $userGroup->setUserCount($userCount);
        }

        $view = new View($userGroups);

        return $view;
    }

    /**
     * Get user group Members.
     *
     * @param Request $request the request object
     *
     * @Route("/user/groups/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getGroupMembersAction(
        Request $request,
        $id
    ) {
        $groupMembers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findBy(array(
                'groupId' => $id,
                'type' => UserGroupHasUser::TYPE_CARD,
            ));

        $result = array();
        foreach ($groupMembers as $groupMember) {
            $groups = $this->getGroupsByUser($groupMember->getUserId());
            $result[] = array(
                'user_id' => $groupMember->getUserId(),
                'groups' => $groups,
            );
        }

        $view = new View($result);

        return $view;
    }

    /**
     * Create group.
     *
     * @param Request $request the request object
     *
     * @Route("/user/groups")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postGroupsAction(
        Request $request
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $em = $this->getDoctrine()->getManager();

        $group = new UserGroup();
        $group->setName($request->get('name'));
        $group->setType(UserGroup::TYPE_CREATED);
        $group->setCompanyId($salesCompanyId);

        $em->persist($group);

        $em->flush();

        $response = array(
            'id' => $group->getId(),
        );

        return new View($response, 201);
    }

    /**
     * Update group.
     *
     * @param Request $request the request object
     *
     * @Route("/user/groups/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putGroupsAction(
        Request $request,
        $id
    ) {
        $group = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroup')
            ->find($id);
        $this->throwNotFoundIfNull($group, self::NOT_FOUND_MESSAGE);

        if ($group->getType() == UserGroup::TYPE_CARD) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_CARD_GROUP_CAN_NOT_BE_EDITED_CODE,
                CustomErrorMessagesConstants::ERROR_CARD_GROUP_CAN_NOT_BE_EDITED_MESSAGE
            );
        }

        $group->setName($request->get('name'));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Remove A group.
     *
     * @param Request $request the request object
     *
     * @Route("/user/groups/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function removeGroupsAction(
        Request $request,
        $id
    ) {
        $group = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroup')
            ->find($id);
        $this->throwNotFoundIfNull($group, self::NOT_FOUND_MESSAGE);

        if ($group->getType() == UserGroup::TYPE_CARD) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_CARD_GROUP_CAN_NOT_BE_EDITED_CODE,
                CustomErrorMessagesConstants::ERROR_CARD_GROUP_CAN_NOT_BE_EDITED_MESSAGE
            );
        }

        $em = $this->getDoctrine()->getManager();

        $groupUsers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findBy(array('groupId' => $id));

        foreach ($groupUsers as $groupUser) {
            $em->remove($groupUser);
        }

        $em->remove($group);

        $em->flush();
    }

    /**
     * Add group users.
     *
     * @param Request $request the request object
     *
     * @Route("/user/groups/user")
     * @Method({"POST"})
     *
     * @return View
     */
    public function addGroupUsers(
        Request $request
    ) {
        $userId = $request->get('user_id');
        $adds = $request->get('add');
        $removes = $request->get('remove');

        $em = $this->getDoctrine()->getManager();

        foreach ($adds as $add) {
            $groupUser = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findOneBy(
                    array(
                        'userId' => $userId,
                        'groupId' => $add,
                        'type' => UserGroupHasUser::TYPE_ADD,
                    )
                );

            if (!$groupUser) {
                $this->get('sandbox_api.group_user')->storeGroupUser(
                    $em,
                    $add,
                    $userId,
                    UserGroupHasUser::TYPE_ADD,
                    new \DateTime('now'),
                    new \DateTime('now')
                );
            }
        }

        foreach ($removes as $remove) {
            $groupUser = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findOneBy(
                    array(
                        'userId' => $userId,
                        'groupId' => $remove,
                        'type' => UserGroupHasUser::TYPE_ADD,
                    )
                );

            if ($groupUser) {
                $em->remove($groupUser);
            }
        }

        $em->flush();
    }

    /**
     * @param $user
     *
     * @return array
     */
    private function getGroupsByUser(
        $user
    ) {
        $groupMembers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->getGroupsByUser(
                $user,
                UserGroupHasUser::TYPE_CARD
            );
        $group = array();
        foreach ($groupMembers as $groupMember) {
            $group[] = array(
                'id' => $groupMember['id'],
                'name' => $groupMember['name'],
            );
        }

        return $group;
    }
}
