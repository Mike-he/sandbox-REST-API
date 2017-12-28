<?php

namespace Sandbox\ClientApiBundle\Controller\CustomerService;

use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

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
        $em = $this->getDoctrine()->getManager();

        $appKey = $this->getParameter('jpush_property_key');

        /**
         * 1. check user is not banned done
         * 2. check building exist and get building done
         * 3. get company
         * 4. get customer services of the building
         * 5. create chat group
         * 6. save chat group members
         * 7. create Jmessage chat group.
         * 8. add Customer Service member into Jmessage group.
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
            throw new BadRequestHttpException(
                CustomErrorMessagesConstants::ERROR_CUSTOMER_SERVICE_PAYLOAD_NOT_CORRECT_CODE
            );
        }

        $tag = $data['tag'];

        $platform = $data['platform'];
        if (is_null($platform)) {
            $platform = PlatformConstants::PLATFORM_OFFICIAL;
        }

        $building = $this->getRoomBuildingRepo()
            ->find($data['building_id']);

        if (is_null($building)) {
            throw new BadRequestHttpException(
                CustomErrorMessagesConstants::ERROR_SALES_COMPANY_ROOM_BUILDING_NOT_FOUND_MESSAGE
            );
        }

        // get customer services
        $customerServices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->findBy(
                array(
                    'buildingId' => $building->getId(),
                    'tag' => $tag,
                )
            );

        if (empty($customerServices)) {
            throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
        }

        $existChatGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
            ->findOneBy([
                'buildingId' => $building->getId(),
                'creatorId' => $myUserId,
                'tag' => $tag,
                'platform' => $platform,
            ]);

        if (!is_null($existChatGroup)) {
            $gid = $existChatGroup->getGid();
            if (!$gid) {
                $gid = $this->createXmppChatGroup($existChatGroup,$platform);

                $existChatGroup->setGid($gid);
                $em->flush();

                $chatGroupMembers = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:ChatGroup\ChatGroupMember')
                    ->findBy(array('chatGroup' => $existChatGroup));

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

                $this->addXmppChatGroupMember($existChatGroup, $memberIds, $appKey);
            }

            return new View([
                'id' => $existChatGroup->getId(),
                'name' => $existChatGroup->getName(),
                'gid' => $gid,
            ]);
        }

        // get company
        $company = $building->getCompany();

        // create new chat group
        $chatGroupName = $building->getName().'客服';

        $chatGroup = new ChatGroup();
        $chatGroup->setCreator($myUser);
        $chatGroup->setName($chatGroupName);
        $chatGroup->setBuildingId($building->getId());
        $chatGroup->setCompanyId($company->getId());
        $chatGroup->setTag($tag);
        $chatGroup->setPlatform($platform);

        $em->persist($chatGroup);

        // set chat group members
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
                $chatGroupMember->setAddBy($myUser);

                $em->persist($chatGroupMember);
            }
        }
        // save to db
        $em->flush();

        // create chat group in Openfire
        $gid = $this->createXmppChatGroup($chatGroup,$platform);
        $chatGroup->setGid($gid);

        $this->addXmppChatGroupMember($chatGroup, $memberIds, $appKey);

        $em->flush();

        // response
        $view = new View();
        $view->setStatusCode(201);
        $view->setData(array(
            'id' => $chatGroup->getId(),
            'name' => $chatGroupName,
            'gid' => $gid,
        ));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=false,
     *    description="building id"
     * )
     *
     * @Route("/customerservice/status")
     * @Method({"GET"})
     *
     * @return View
     */
    public function checkCustomerServiceStatusAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');

        // get customer services
        $customerServices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
            ->getServicesByBuilding($buildingId);

        $services = [];
        foreach ($customerServices as $customerService) {
            array_push($services, $customerService['tag']);
        }

        return new View($services);
    }
}
