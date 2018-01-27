<?php

namespace Sandbox\ClientApiBundle\Controller\ChatGroup;

use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ClientApiBundle\Data\ChatGroup\ChatGroupData;
use Sandbox\ApiBundle\Form\ChatGroup\ChatGroupType;
use Sandbox\ClientApiBundle\Form\ChatGroup\ChatGroupDataType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Rs\Json\Patch;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;

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
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // get request payload
        $data = new ChatGroupData();

        $form = $this->createForm(new ChatGroupDataType(), $data);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // validate request content
        $memberIds = $data->getMemberIds();
        $name = $data->getName();
        $platform = $data->getPlatform();

        if (is_null($platform)) {
            $platform = PlatformConstants::PLATFORM_OFFICIAL;
        }

        if (is_null($memberIds) || empty($memberIds)) {
            // TODO return custom error
        }

        // create new chat group
        $em = $this->getDoctrine()->getManager();

        $chatGroup = new ChatGroup();
        $chatGroup->setCreator($myUser);
        $chatGroup->setTag(ChatGroup::GROUP_SERVICE);
        $chatGroup->setPlatform($platform);

        // add member
        $chatGroupName = $name;
        $memberCount = 0;

        $allMembersIds = array($myUserId);
        $allMembersIds = array_merge($allMembersIds, $memberIds);

        foreach ($allMembersIds as $memberId) {
            try {
                if ($memberId != $myUserId) {
                    $member = $this->getRepo('User\User')->find($memberId);
                    if (is_null($member)) {
                        continue;
                    }

                    // check member is buddy
                    $buddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                        'user' => $myUser,
                        'buddy' => $member,
                    ));
                    if (is_null($buddy)) {
                        continue;
                    }
                } else {
                    $member = $myUser;
                }

                $memberProfile = $this->getRepo('User\UserProfile')->findOneByUser($member);
                if (is_null($memberProfile)) {
                    continue;
                }

                // save member
                $this->saveChatGroupMember($em, $chatGroup, $member, $myUser);

                // generate name
                ++$memberCount;

                if (is_null($name)) {
                    $chatGroupName = $chatGroupName.$memberProfile->getName();

                    if ($memberCount < sizeof($allMembersIds)) {
                        $chatGroupName = $chatGroupName.', ';
                    }
                }
            } catch (\Exception $e) {
                error_log($e);
                continue;
            }
        }

        $chatGroup->setName($chatGroupName);
        $em->persist($chatGroup);

        // save to db
        $em->flush();

        $result = $this->createXmppChatGroup($chatGroup, $platform);

        if (!isset($result['gid'])) {
            $em->remove($chatGroup);
            $em->flush();

            throw new BadRequestHttpException(
                CustomErrorMessagesConstants::ERROR_JMESSAGE_ERROR_MESSAGE
            );
        }

        $chatGroup->setGid($result['gid']);

        $em->flush();

        // response
        $view = new View();
        $view->setData(array(
            'id' => $chatGroup->getId(),
            'name' => $chatGroupName,
            'gid' => $chatGroup->getGid(),
        ));

        return $view;
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
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View(array());
        }

        // get my chat groups
        $chatGroups = $this->getRepo('ChatGroup\ChatGroup')->getMyChatGroups($myUserId);

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
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned and authorized
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chat group
        $chatGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->find($id);
        if (!$chatGroup) {
            return new View();
        }

        if ($chatGroup->getTag() != ChatGroup::CUSTOMER_SERVICE) {
            // set group name
            if (is_null($chatGroup->getName()) || $chatGroup->getName()) {
                $chatGroupName = $this->constructGroupChatName(
                    $chatGroup
                );

                $chatGroup->setName($chatGroupName);
            }
        }

        if ($chatGroup->getBuildingId()) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($chatGroup->getBuildingId());
            if ($building) {
                $chatGroup->setBuildingAvatar($building->getAvatar());
            }
        }

        // set view
        $view = new View($chatGroup);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('chatgroup'))
        );

        return $view;
    }

    /**
     * Retrieve everything a given chat group.
     *
     * @param Request $request the request object
     * @param int     $gid
     *
     * @Route("/chatgroups/{gid}/all")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupAllAction(
        Request $request,
        $gid
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chatGroup
        $chatGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->findOneBy(array('gid' => $gid));
        $this->throwNotFoundIfNull($chatGroup, self::NOT_FOUND_MESSAGE);

        // get chat group and members array for response
        $chatGroupArray = array(
            'id' => $chatGroup->getId(),
            'name' => $chatGroup->getName(),
            'creator_id' => $chatGroup->getCreatorId(),
            'creator_name' => $chatGroup->getCreator()->getUserProfile()->getName(),
        );

        $members = $this->getRepo('ChatGroup\ChatGroupMember')->findByChatGroup($chatGroup);
        if (!is_null($members) && !empty($members)) {
            $chatGroupArray['members'] = $this->getChatGroupMembersArray($members);
        }

        // set group name
        if (is_null($chatGroup->getName()) || empty($chatGroup->getName())) {
            $chatGroupName = $this->constructGroupChatName(
                $chatGroup->getId()
            );

            $chatGroupArray['name'] = $chatGroupName;
        }

        // set view
        $view = new View($chatGroupArray);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('chatgroup'))
        );

        return $view;
    }

    /**
     * Modify a given chat group.
     *
     * @param Request $request
     * @param int     $gid
     *
     * @Route("/chatgroups/{gid}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchChatGroupAction(
        Request $request,
        $gid
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chatGroup
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->findOneBy(array('gid' => $gid));
        $this->throwNotFoundIfNull($chatGroup, self::NOT_FOUND_MESSAGE);

        $userId = $chatGroup->getCreatorId();

        // bind data
        $chatGroupJson = $this->container->get('serializer')->serialize($chatGroup, 'json');
        $patch = new Patch($chatGroupJson, $request->getContent());
        $chatGroupJson = $patch->apply();

        $form = $this->createForm(new ChatGroupType(), $chatGroup);
        $form->submit(json_decode($chatGroupJson, true));

        // set chatGroup
        $chatGroup->setModificationDate(new \DateTime('now'));
        $chatGroup->setCreatorId($userId);

        // update to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->updateXmppChatGroup($chatGroup);

        return new View();
    }

    /**
     * Remove / quit a given chat group.
     *
     * @param Request $request the request object
     * @param int     $gid
     *
     * @Route("/chatgroups/{gid}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteChatGroupAction(
        Request $request,
        $gid
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            return new View();
        }

        // get chatGroup
        $chatGroup = $this->getRepo('ChatGroup\ChatGroup')->findOneBy(array('gid' => $gid));
        if (is_null($chatGroup)) {
            return new View();
        }

        // only chat group creator is allowed to remove it
        if ($myUser != $chatGroup->getCreator()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $this->deleteXmppChatGroup($gid);

        // remove from db
        $em = $this->getDoctrine()->getManager();
        $em->remove($chatGroup);
        $em->flush();

        return new View();
    }

    /**
     * @param ChatGroup $chatGroup
     *
     * @return string
     */
    protected function constructGroupChatName(
        $chatGroup
    ) {
        $members = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
            ->findBy(array(
                'chatGroup' => $chatGroup,
            ));

        $groupNameString = '';
        $memberCount = 0;

        foreach ($members as $member) {
            ++$memberCount;

            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')->findOneBy(array(
                    'user' => $member->getUser(),
                ));
            $userName = $profile->getName();

            if (is_null($userName)) {
                continue;
            }

            $groupNameString = $groupNameString.$userName;

            if ($memberCount < sizeof($members)) {
                $groupNameString = $groupNameString.', ';
            }
        }

        return $groupNameString;
    }
}
