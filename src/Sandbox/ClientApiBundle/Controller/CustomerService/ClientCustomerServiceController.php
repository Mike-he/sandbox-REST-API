<?php

namespace Sandbox\ClientApiBundle\Controller\CustomerService;

use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServiceMember;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Client Customer Service Controller.
 */
class ClientCustomerServiceController extends ChatGroupController
{
    use HasAccessToEntityRepositoryTrait;

    /**
     * Create a chat group.
     *
     * @param Request $request the request object
     *
     * @Route("/customerservice")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postCustomerServiceAction(
        Request $request
    ) {

        /**
         * 1. check user is not banned done
         * 2. check building exist and get building done
         * 3. get company
         * 4. get customer services of the building
         * 5. create chat group
         * 6. save chat group members
         * 7. create openfire chat group.
         */
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // check banned
        if ($myUser->isBanned()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // get building
        $data = json_decode($request->getContent(), true);
        if (!isset($data['tag'])) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_CUSTOMER_SERVICE_PAYLOAD_NOT_CORRECT_CODE);
        }

        $building = $this->getRoomBuildingRepo()
            ->find($data['building_id']);

        if (is_null($building)) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_SALES_COMPANY_ROOM_BUILDING_NOT_FOUND_MESSAGE);
        }

        // get customer services
        $customerServices = $this->getServiceMemberRepo()->findBy(
            array(
                'buildingId' => $building->getId(),
                'tag' => $data['tag'],
            )
        );

        // get services group members
        $members = [];
        $tag = RoomBuildingServiceMember::SERVICE;
        foreach ($customerServices as $customerService) {
            $memberId = $customerService->getUserId();
            $members[] = $this->getUserRepo()->find($memberId);
        }

        // get company
        $company = $building->getCompany();

        // create new chat group
        $em = $this->getDoctrine()->getManager();
        $chatGroupName = $building->getName();

        $chatGroup = new ChatGroup();
        $chatGroup->setCreator($myUser);
        $chatGroup->setName($chatGroupName);
        $chatGroup->setBuildingId($building->getId());
        $chatGroup->setCompanyId($company->getId());
        $chatGroup->setTag($tag);

        $em->persist($chatGroup);

        // set chat group members
        foreach ($members as $member) {
            $chatGroupMember = new ChatGroupMember();
            $chatGroupMember->setChatGroup($chatGroup);
            $chatGroupMember->setUser($member);
            $chatGroupMember->setAddBy($myUser);

            $em->persist($chatGroupMember);
        }

        // save to db
        $em->flush();

        // create chat group in Openfire
        $this->createXmppChatGroup(
            $chatGroup,
            ChatGroup::XMPP_CUSTOMER_SERVICE
        );

        // response
        $view = new View();
        $view->setData(array(
            'id' => $chatGroup->getId(),
            'name' => $chatGroupName,
        ));

        return $view;
    }
}
