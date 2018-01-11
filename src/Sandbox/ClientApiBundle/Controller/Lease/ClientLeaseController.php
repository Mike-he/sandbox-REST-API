<?php

namespace Sandbox\ClientApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserGroupHasUser;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\LeaseNotificationTrait;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;

class ClientLeaseController extends SandboxRestController
{
    use DoorAccessTrait;
    use LeaseNotificationTrait;
    use GenerateSerialNumberTrait;
    use LeaseTrait;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="offset",
     *     default="0",
     *     nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="limit",
     *     default="10",
     *     nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="status"
     * )
     *
     * @Route("/leases")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getClientLeasesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $customerIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->getCustomerIdsByUserId($userId);

        if (empty($customerIds)) {
            return new View();
        }

        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');
        $status = $paramFetcher->get('status');

        $longTermNumbersArray = $this->generateLongTermNumbersArray($customerIds, $status, $offset, $limit);

        $response = array();
        foreach ($longTermNumbersArray as $number) {
            $response[] = $this->getLeaseResponseArray($number);
        }

        $view = new View($response);

        return $view;
    }

    /**
     * Get Lease Detail.
     *
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeaseAction(
        $id
    ) {
        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $lesseeCustomer = $lease->getLesseeCustomer();
        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($lesseeCustomer);
        $lease->setLesseeCustomer($customer->getUserId());

        $bills = $this->getLeaseBillRepo()
            ->findBy(array(
                'lease' => $lease,
                'type' => LeaseBill::TYPE_LEASE,
            ));
        $lease->setBills($bills);

        $unpaidBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                null,
                LeaseBill::STATUS_UNPAID
            );
        $lease->setUnpaidLeaseBillsAmount($unpaidBills);

        $totalLeaseBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_LEASE
            );
        $lease->setTotalLeaseBillsAmount($totalLeaseBills);

        $this->setLeaseLogs($lease);

        if ($lease->getLesseeEnterprise()) {
            $enterprise = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
                ->find($lease->getLesseeEnterprise());

            $enterpriseName = $enterprise ? $enterprise->getName() : '';
            $lease->setLesseeEnterpriseName($enterpriseName);
        }

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );
        $view->setData($lease);

        return $view;
    }

    /**
     * Add Invited People.
     *
     * @Route("/leases/{id}/people")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function invitePeopleAction(
        Request $request,
        $id
    ) {
        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->find($id);
        $this->throwNotFoundIfNull($lease);

        // check user permission
        $leaseUserId = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->getUserIdByCustomerId($lease->getLesseeCustomer());
        $this->throwAccessDeniedIfNotSameUser($leaseUserId);

        $status = $lease->getStatus();
        $endDate = $lease->getEndDate();
        $now = new \DateTime();

        // limit inviting people conditions
        if ($status !== Lease::LEASE_STATUS_PERFORMING ||
            $now >= $endDate
        ) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        $people = json_decode($request->getContent(), true);

        if ($leaseUserId) {
            $this->setDoorAccessForInvite(
                $lease,
                $leaseUserId,
                $people['add'],
                $people['remove']
            );
        }

        return new View();
    }

    /**
     * @param Lease $lease
     * @param int   $userId
     * @param array $users
     * @param array $removeUsers
     */
    private function setDoorAccessForInvite(
        $lease,
        $userId,
        $users,
        $removeUsers
    ) {
        $base = $lease->getBuilding()->getServer();
        $recvUsers = [];

        // invite people
        if (!empty($users) && !is_null($users)) {
            $recvUsers = $this->addPeople(
                $users,
                $lease,
                $base
            );
        }

        // remove people
        $removedUserArray = [];
        if (!empty($removeUsers) && !is_null($removeUsers)) {
            // remove user
            $removedUserArray = $this->removeInvitedPeople(
                $removeUsers,
                $lease,
                $base
            );
        }

        // send notification to invited users
        if (!empty($recvUsers)) {
            $this->sendXmppLeaseNotification(
                $lease,
                $recvUsers,
                ProductOrder::ACTION_INVITE_ADD,
                $userId,
                [],
                ProductOrderMessage::APPOINT_MESSAGE_PART1,
                ProductOrderMessage::APPOINT_MESSAGE_PART2
            );
        }

        // send notification to removed users
        if (!empty($removedUserArray)) {
            $this->sendXmppLeaseNotification(
                $lease,
                $removedUserArray,
                ProductOrder::ACTION_INVITE_REMOVE,
                $userId,
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }
    }

    /**
     * @param $users
     * @param Lease $lease
     * @param $base
     *
     * @return array|mixed
     */
    private function addPeople(
        $users,
        $lease,
        $base
    ) {
        $em = $this->getDoctrine()->getManager();

        $userArray = [];
        $recvUsers = [];

        $roomDoors = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomDoors')
            ->findBy(['room' => $lease->getRoom()]);

        $invitedPeople = $lease->getInvitedPeople();
        foreach ($users as $userId) {
            // find user
            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')->find($userId);
            $this->throwNotFoundIfNull($user, User::ERROR_NOT_FOUND);

            // check and add user in sales customer
            $this->get('sandbox_api.sales_customer')->createCustomer($userId, $lease->getCompanyId());

            // find user in invitedPeople
            if (!$invitedPeople->contains($user)) {
                $lease->addInvitedPeople($user);
                $em->persist($lease);

                // set user array for message
                array_push($recvUsers, $userId);
            }

            if (is_null($base) || empty($base) || empty($roomDoors)) {
                continue;
            }

            $this->storeDoorAccess(
                $em,
                $lease->getAccessNo(),
                $userId,
                $lease->getBuildingId(),
                $lease->getRoomId(),
                $lease->getStartDate(),
                $lease->getEndDate()
            );

            $userArray = $this->getUserArrayIfAuthed(
                $base,
                $userId,
                $userArray
            );
        }

        $em->flush();

        // set room access
        if (!empty($userArray)) {
            $this->callSetRoomOrderCommand(
                $base,
                $userArray,
                $roomDoors,
                $lease->getAccessNo(),
                $lease->getStartDate(),
                $lease->getEndDate()
            );
        }

        // Add user to User Group
        $this->setDoorAccessForMembershipCard(
            $lease->getBuildingId(),
            $users,
            $lease->getStartDate(),
            $lease->getEndDate(),
            $lease->getSerialNumber(),
            UserGroupHasUser::TYPE_LEASE
        );

        return $recvUsers;
    }

    /**
     * @param $removeUsers
     * @param $lease
     * @param $base
     *
     * @return array
     */
    private function removeInvitedPeople(
        $removeUsers,
        $lease,
        $base
    ) {
        $em = $this->getDoctrine()->getManager();

        $userArray = [];
        $recvUsers = [];
        foreach ($removeUsers as $removeUserId) {
            $removeUser = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')->find($removeUserId);
            $this->throwNotFoundIfNull($removeUser);

            $hasAccess = $lease->getInvitedPeople()->contains($removeUser);

            if ($hasAccess) {
                $em = $this->getDoctrine()->getManager();

                $lease->removeInvitedPeople($removeUser);

                $em->flush();

                // set user array for message
                array_push($recvUsers, $removeUserId);
            }

            if (is_null($base) || empty($base)) {
                continue;
            }

            // set action of door access to delete
            $this->setAccessActionToDelete(
                $lease->getAccessNo(),
                $removeUserId,
                DoorAccessConstants::METHOD_DELETE
            );

            $result = $this->getCardNoByUser($removeUserId);
            if ($result['status'] !== DoorController::STATUS_UNAUTHED) {
                $empUser = ['empid' => $removeUserId];
                array_push($userArray, $empUser);
            }
        }

        $em->flush();

        // remove room access
        if (!empty($userArray)) {
            $this->callRemoveFromOrderCommand(
                $base,
                $lease->getAccessNo(),
                $userArray
            );
        }

        // Remove user to User Group
        $door = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->getGroupsByBuilding(
                $lease->getBuildingId(),
                true
            );

        if ($door) {
            $now = new \DateTime('now');
            $card = $door->getCard();

            $this->addUserToUserGroup(
                $em,
                $removeUsers,
                $card,
                $lease->getStartDate(),
                $now,
                $lease->getSerialNumber(),
                UserGroupHasUser::TYPE_LEASE
            );
        }

        return $recvUsers;
    }

    /**
     * @param $userId
     * @param $status
     * @param $offset
     * @param $limit
     *
     * @return array
     */
    private function generateLongTermNumbersArray(
        $userId,
        $status,
        $offset,
        $limit
    ) {
        $validLeaseNumbers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->getLeaseNumbersForClientLease(
                $userId,
                array(
                    Lease::LEASE_STATUS_PERFORMING,
                    Lease::LEASE_STATUS_MATURED,
                )
            );

        $invalidLeaseNumbers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->getLeaseNumbersForClientLease(
                $userId,
                array(
                    Lease::LEASE_STATUS_END,
                    Lease::LEASE_STATUS_TERMINATED,
                    Lease::LEASE_STATUS_CLOSED,
                )
            );

        $longTermArray = array();
        $longTermArray = array_merge($longTermArray, $validLeaseNumbers);

        if ($status == ProductAppointment::STATUS_PENDING) {
            // for pagination
            $numbers = array();
            for ($i = $offset; $i < $offset + $limit; ++$i) {
                if (isset($longTermArray[$i])) {
                    array_push($numbers, $longTermArray[$i]);
                }
            }

            return $numbers;
        }

        $longTermArray = array_merge($longTermArray, $invalidLeaseNumbers);

        // for pagination
        $numbers = array();
        for ($i = $offset; $i < $offset + $limit; ++$i) {
            if (isset($longTermArray[$i])) {
                array_push($numbers, $longTermArray[$i]);
            }
        }

        return $numbers;
    }

    /**
     * @param $number
     *
     * @return array
     */
    private function getLeaseResponseArray(
        $number
    ) {
        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findOneBy(array(
                'serialNumber' => $number,
            ));

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->getClientLeaseBills($lease, LeaseBill::STATUS_UNPAID);

        $response = array(
            'id' => $lease->getId(),
            'serial_number' => $lease->getSerialNumber(),
            'status' => $lease->getStatus(),
            'product' => $lease->degenerateProduct(),
            'start_date' => $lease->getStartDate(),
            'end_date' => $lease->getEndDate(),
            'unpaid_lease_bills_amount' => count($bills),
            'creation_date' => $lease->getCreationDate(),
            'confirming_date' => $lease->getConfirmingDate(),
            'monthly_rent' => $lease->getMonthlyRent(),
            'bills' => $bills,
        );

        return $response;
    }
}
