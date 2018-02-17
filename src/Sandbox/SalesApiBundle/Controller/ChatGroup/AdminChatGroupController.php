<?php

namespace Sandbox\SalesApiBundle\Controller\ChatGroup;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\AdminApiBundle\Command\SyncJmessageUserCommand;
use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sandbox\ApiBundle\Form\ChatGroup\ChatGroupType;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin Chat Group Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminChatGroupController extends ChatGroupController
{
    /**
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="media_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by tag"
     * )
     *
     * @Route("/chatgroups/media")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMediaAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $mediaId = $paramFetcher->get('media_id');

        $media = $this->get('sandbox_api.jmessage')->getMedia($mediaId);

        $result = $media['body'];

        return new View($result);
    }

    /**
     * Retrieve all other service members by sales company.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups/service/members")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getServiceMembersAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'id' => $userId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $myServices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findBy(['userId' => $userId]);

        $buildings = [];
        foreach ($myServices as $myService) {
            $tag = $myService->getTag();
            $buildingId = $myService->getBuildingId();

            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->findOneBy([
                    'id' => $buildingId,
                    'companyId' => $companyId,
                ]);

            if (is_null($building)) {
                continue;
            }

            array_push($buildings, ['building_id' => $buildingId, 'tag' => $tag]);
        }

        $memberArray = [];
        foreach ($buildings as $building) {
            $members = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
                ->findBy([
                    'buildingId' => $building['building_id'],
                    'tag' => $building['tag'],
                ]);

            foreach ($members as $member) {
                array_push($memberArray, $member->getUserId());
            }
        }

        $memberArray = array_unique($memberArray, SORT_REGULAR);

        $finalMembers = [];
        foreach ($memberArray as $item) {
            $salesAdminProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy([
                    'userId' => $item,
                    'salesCompanyId' => $companyId,
                ]);

            if (!$salesAdminProfile) {
                $salesAdminProfile = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy([
                        'userId' => $item,
                        'salesCompanyId' => null,
                    ]);
            }

            $salesAdmin = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                ->findOneBy([
                    'userId' => $item,
                ]);

            $finalMembers[] = array(
                'user_id' => $item,
                'username' => $salesAdminProfile ? $salesAdminProfile->getNickname() : null,
                'avatar' => $salesAdminProfile ? $salesAdminProfile->getAvatar() : null,
                'xmpp_user' => $salesAdmin ? $salesAdmin->getXmppUsername() : null,
            );
        }

        return new View($finalMembers);
    }

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
     * @Annotations\QueryParam(
     *    name="tag",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by tag"
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

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];
        $search = $paramFetcher->get('search');
        $tag = $paramFetcher->get('tag');

        // get my chat groups
        $chatGroups = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->getAdminChatGroups(
                $companyId,
                $userId,
                $search,
                $tag
            );

        // response
        return new View($chatGroups);
    }

    /**
     * Retrieve a given chat group.
     *
     * @param Request $request the request object
     * @param string  $gid
     *
     * @Route("/chatgroups/{gid}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getChatGroupAction(
        Request $request,
        $gid
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'id' => $userId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        // get chat group
        $chatGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->getAdminChatGroupById(
                $gid,
                $companyId,
                $userId
            );
        if (is_null($chatGroup) || empty($chatGroup)) {
            return new View();
        }

        $members = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
            ->getChatGroupMembersByGroup($chatGroup['id']);

        $chatGroup['group_members'] = $members;

        return new View($chatGroup);
    }

    /**
     * create chat group.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups")
     * @Method({"POST"})
     *
     * @return View
     */
    public function createChatGroupAction(
        Request $request
    ) {
        $em = $this->getDoctrine()->getManager();
        $appKey = $this->getParameter('jpush_property_key');

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

        $chatGroup = new ChatGroup();
        $form = $this->createForm(new ChatGroupType(), $chatGroup);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $buildingId = $chatGroup->getBuildingId();

        // find building
        $building = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($buildingId);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        // find user as creator
        $creatorId = $chatGroup->getCreatorId();
        $creator = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'id' => $creatorId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($creator, self::NOT_FOUND_MESSAGE);

        // check if admin is service member
        $member = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findOneBy([
                'userId' => $userId,
                'buildingId' => $buildingId,
                'tag' => $chatGroup->getTag(),
            ]);
        $this->throwAccessDeniedIfNull($member);

        //get creator latest login client
        $userPlatform = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserPlatform')
            ->findOneBy(array('userId' => $creatorId));
        $platform = $userPlatform ? $userPlatform->getPlatform() : PlatformConstants::PLATFORM_OFFICIAL;

        // check if chat group already exist
        $existGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->findOneBy([
                'creator' => $creator,
                'buildingId' => $buildingId,
                'tag' => $chatGroup->getTag(),
                'platform' => $platform,
            ]);

        if (!is_null($existGroup)) {
            $gid = $existGroup->getGid();
            if (!$gid) {
                $result = $this->createXmppChatGroup($existGroup, $platform);

                if (!isset($result['gid'])) {
                    $em->remove($chatGroup);
                    $em->flush();

                    throw new BadRequestHttpException(
                        CustomErrorMessagesConstants::ERROR_JMESSAGE_ERROR_MESSAGE
                    );
                }
                $existGroup->setGid($result['gid']);
                $em->flush();

                $chatGroupMembers = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
                    ->findBy(array('chatGroup' => $existGroup));

                $memberIds = [];
                foreach ($chatGroupMembers as $chatGroupMember) {
                    $userId = $chatGroupMember->getUser();

                    $salesAdmin = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                        ->findOneBy(array('userId' => $userId));
                    if ($salesAdmin) {
                        $memberIds[] = $salesAdmin->getXmppUsername();
                    }
                }

                $this->addXmppChatGroupMember($existGroup, $memberIds, $appKey);
            }

            return new View([
                    'id' => $existGroup->getId(),
                    'name' => $existGroup->getName(),
                    'gid' => $existGroup->getGid(),
                ]);
        }

        // set new chat group
        $chatGroup->setCompanyId($companyId);
        $chatGroup->setCreator($creator);
        $chatGroup->setName($building->getName().'客服');
        $chatGroup->setPlatform($platform);

        $em->persist($chatGroup);

        // set members
        $customerServices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findBy([
                'buildingId' => $buildingId,
                'tag' => $chatGroup->getTag(),
            ]);

        $memberIds = [];
        foreach ($customerServices as $customerService) {
            $userId = $customerService->getUserId();

            $salesAdmin = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                ->findOneBy(array('userId' => $userId));
            if ($salesAdmin) {
                $memberIds[] = $salesAdmin->getXmppUsername();

                $chatGroupMember = new ChatGroupMember();
                $chatGroupMember->setChatGroup($chatGroup);
                $chatGroupMember->setUser($userId);
                $chatGroupMember->setAddBy($creator);

                $em->persist($chatGroupMember);
            }
        }

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

        $this->addXmppChatGroupMember($chatGroup, $memberIds, $appKey);

        $em->flush();

        //execute SyncJmessageUserCommand
        $command = new SyncJmessageUserCommand();
        $command->setContainer($this->container);

        $input = new ArrayInput(array('userId' => $chatGroup->getCreatorId()));
        $output = new NullOutput();

        $command->run($input, $output);

        // response
        $view = new View();
        $view->setStatusCode(201);
        $view->setData(array(
            'id' => $chatGroup->getId(),
            'name' => $chatGroup->getName(),
            'gid' => $chatGroup->getGid(),
        ));

        return $view;
    }

    /**
     * List buildings of my services.
     *
     * @param Request $request the request object
     *
     * @Route("/chatgroups/service/my")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyServicesAction(
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

        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findBy(['userId' => $userId]);

        $myServices = [];
        foreach ($services as $service) {
            $buildingId = $service->getBuildingId();
            $tag = $service->getTag();

            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($buildingId);

            if (is_null($building) || $companyId !== $building->getCompanyId()) {
                continue;
            }

            if (!array_key_exists($tag, $myServices)) {
                $myServices[$tag] = [$buildingId];
            } else {
                array_push($myServices[$tag], $buildingId);
            }
        }

        // response
        return new View($myServices);
    }
}
