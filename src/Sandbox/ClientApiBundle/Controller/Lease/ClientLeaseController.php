<?php

namespace Sandbox\ClientApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Sandbox\ApiBundle\Traits\LeaseNotificationTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientLeaseController extends SandboxRestController
{
    use HasAccessToEntityRepositoryTrait;
    use DoorAccessTrait;
    use LeaseNotificationTrait;
    use GenerateSerialNumberTrait;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="ids",
     *     array=true
     * )
     *
     * @Route("/leases/time_remaining")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeaseTimeRemainingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('ids');

        $expireInParameter = $this->getParameterRepo()
            ->findOneBy(array(
                'key' => Parameter::KEY_LEASE_CONFIRM_EXPIRE_IN,
            ));

        $response = array();
        foreach ($ids as $id) {
            $lease = $this->getLeaseRepo()
                ->findOneBy(array(
                    'id' => $id,
                    'status' => Lease::LEASE_STATUS_CONFIRMING,
                ));

            if (is_null($lease)) {
                continue;
            }

            $modificationDate = $lease->getModificationDate()->setTime(23, 59, 59);
            $leaseExpireInDate = $modificationDate->add(new \DateInterval('P'.$expireInParameter->getValue()));

            $now = new \DateTime('now');
            $diffDate = $now->diff($leaseExpireInDate);

            array_push($response, array(
                'lease_id' => $id,
                'remaining_days' => $diffDate->d,
                'remaining_hours' => $diffDate->h,
                'remaining_minutes' => $diffDate->i,
                'remaining_seconds' => $diffDate->s,
            ));
        }

        if (empty($response)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return new View($response);
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
        $lease = $this->getLeaseRepo()
            ->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkUserLeasePermission($lease);

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

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );
        $view->setData($lease);

        return $view;
    }

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
     * @Route("/leases")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeasesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');

        $leases = $this->getLeaseRepo()
            ->getClientLeases(
                $userId,
                $limit,
                $offset
            );

        $response = array();
        foreach ($leases as $lease) {
            $bills = $this->getLeaseBillRepo()
                ->findBy(array(
                    'lease' => $lease,
                    'status' => LeaseBill::STATUS_UNPAID,
                ));

            array_push($response, array(
                'id' => $lease->getId(),
                'serial_number' => $lease->getSerialNumber(),
                'status' => $lease->getStatus(),
                'product' => $lease->degenerateProduct(),
                'start_date' => $lease->getStartDate(),
                'end_date' => $lease->getEndDate(),
                'unpaid_lease_bills_amount' => count($bills),
                'creation_date' => $lease->getCreationDate(),
                'confirming_date' => $lease->getConfirmingDate(),
            ));
        }

        return new View($response);
    }

    /**
     * Patch Lease Status.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}/status")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchLeaseStatusAction(
        Request $request,
        $id
    ) {
        $payload = json_decode($request->getContent(), true);

        if (
            !key_exists('status', $payload) ||
            !filter_var($payload['status'], FILTER_DEFAULT)
        ) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $lease = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\Lease')->find($id);
        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

        // check user permission
        $this->checkUserLeasePermission($lease);

        $status = $lease->getStatus();

        if (
            $payload['status'] != Lease::LEASE_STATUS_CONFIRMED ||
            ($status != Lease::LEASE_STATUS_RECONFIRMING &&
            $status != Lease::LEASE_STATUS_CONFIRMING)
        ) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        if ($status == Lease::LEASE_STATUS_CONFIRMING) {
            $lease->setAccessNo($this->generateAccessNumber());

            $base = $lease->getBuilding()->getServer();
            $roomDoors = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomDoors')
                ->findBy(['room' => $lease->getRoom()]);

            if (!is_null($base) && !empty($base) && !empty($roomDoors)) {
                $this->storeDoorAccess(
                    $em,
                    $lease->getAccessNo(),
                    $lease->getSupervisorId(),
                    $lease->getBuildingId(),
                    $lease->getRoomId(),
                    $lease->getStartDate(),
                    $lease->getEndDate()
                );

                $em->flush();

                $userArray = $this->getUserArrayIfAuthed(
                    $base,
                    $lease->getSupervisorId(),
                    []
                );

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
            }
        }

        $lease->setStatus($payload['status']);
        $lease->setConformedDate(new \DateTime('now'));
        $em->flush();

        return new View();
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
        $lease = $this->getLeaseRepo()->find($id);
        $this->throwNotFoundIfNull($lease);

        // check user permission
        $this->throwAccessDeniedIfNotSameUser($lease->getSupervisorId());

        $status = $lease->getStatus();
        $endDate = $lease->getEndDate();
        $now = new \DateTime();

        // limit inviting people conditions
        if ((
                $status !== Lease::LEASE_STATUS_CONFIRMED &&
                $status !== Lease::LEASE_STATUS_PERFORMING &&
                $status !== Lease::LEASE_STATUS_RECONFIRMING
            ) ||
            $now >= $endDate
        ) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_LEASE_STATUS_NOT_CORRECT_MESSAGE);
        }

        $people = json_decode($request->getContent(), true);

        $this->setDoorAccessForInvite(
            $lease,
            $people['add'],
            $people['remove']
        );

        return new View();
    }

    /**
     * @param Lease $lease
     * @param array $users
     * @param array $removeUsers
     */
    private function setDoorAccessForInvite(
        $lease,
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
                $lease->getSupervisorId(),
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
                $lease->getSupervisorId(),
                [],
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
            );
        }
    }

    /**
     * @param $users
     * @param $lease
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
            $user = $this->getUserRepo()->find($userId);
            $this->throwNotFoundIfNull($user, User::ERROR_NOT_FOUND);

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
            $removeUser = $this->getUserRepo()->find($removeUserId);
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

        return $recvUsers;
    }

    /**
     * @param Lease $lease
     */
    public function checkUserLeasePermission($lease)
    {
        $userId = $this->getUserId();
        if ($userId != $lease->getSupervisorId()
        && $userId != $lease->getDraweeId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }
    }
}
