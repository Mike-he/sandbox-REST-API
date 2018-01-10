<?php

namespace Sandbox\ClientApiBundle\Controller\CustomerService;

use Sandbox\AdminApiBundle\Command\SyncJmessageUserCommand;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup;
use Sandbox\ApiBundle\Entity\ChatGroup\ChatGroupMember;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
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

        $platform = $data['platform'];
        if (is_null($platform)) {
            $platform = PlatformConstants::PLATFORM_OFFICIAL;
        }

        if (!isset($data['tag'])) {
            throw new BadRequestHttpException(
                CustomErrorMessagesConstants::ERROR_CUSTOMER_SERVICE_PAYLOAD_NOT_CORRECT_CODE
            );
        }

        $tag = $data['tag'];

        switch ($tag) {
            case ChatGroup::CUSTOMER_SERVICE:
                $buildingId = $data['building_id'];

                $building = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($buildingId);

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
                            'buildingId' => $buildingId,
                            'tag' => $tag,
                        )
                    );

                if (empty($customerServices)) {
                    throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
                }

                $existChatGroup = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
                    ->findOneBy([
                        'buildingId' => $buildingId,
                        'creatorId' => $myUserId,
                        'tag' => $tag,
                        'platform' => $platform,
                    ]);

                $companyId = $building->getCompanyId();
                $chatGroupName = $building->getName().'客服';

                break;
            case ChatGroup::SERVICE_SERVICE:
                $companyId = $data['company_id'];

                $company = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                    ->find($companyId);

                $customerServices = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuildingServiceMember')
                    ->findBy(
                        array(
                            'companyId' => $companyId,
                            'tag' => $tag,
                        )
                    );

                if (empty($customerServices)) {
                    throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
                }

                $existChatGroup = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:ChatGroup\ChatGroup')
                    ->findOneBy([
                        'companyId' => $companyId,
                        'creatorId' => $myUserId,
                        'tag' => $tag,
                        'platform' => $platform,
                    ]);

                $buildingId = null;
                $chatGroupName = $company->getName().'客服';

                break;
            default:
                throw new BadRequestHttpException(
                    CustomErrorMessagesConstants::ERROR_CUSTOMER_SERVICE_PAYLOAD_NOT_CORRECT_CODE
                );
        }

        if (!is_null($existChatGroup)) {
            $gid = $existChatGroup->getGid();
            if (!$gid) {
                $result = $this->createXmppChatGroup($existChatGroup, $platform);

                if (!isset($result['gid'])) {
                    $em->remove($existChatGroup);
                    $em->flush();

                    throw new BadRequestHttpException(
                        CustomErrorMessagesConstants::ERROR_JMESSAGE_ERROR_MESSAGE
                    );
                }

                $existChatGroup->setGid($result['gid']);
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
                'gid' => $existChatGroup->getGid(),
            ]);
        }

        $chatGroup = new ChatGroup();
        $chatGroup->setCreator($myUser);
        $chatGroup->setName($chatGroupName);
        $chatGroup->setBuildingId($buildingId);
        $chatGroup->setCompanyId($companyId);
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

        if (ChatGroup::CUSTOMER_SERVICE == $tag) {
            //execute SyncJmessageUserCommand
            $command = new SyncJmessageUserCommand();
            $command->setContainer($this->container);

            $input = new ArrayInput(array('userId' => $myUserId));
            $output = new NullOutput();

            $command->run($input, $output);
        }

        // response
        $view = new View();
        $view->setStatusCode(201);
        $view->setData(array(
            'id' => $chatGroup->getId(),
            'name' => $chatGroupName,
            'gid' => $chatGroup->getGid(),
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
