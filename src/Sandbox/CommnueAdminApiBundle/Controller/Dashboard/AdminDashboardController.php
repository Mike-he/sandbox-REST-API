<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Dashboard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminDashboardController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="startDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="endDate"
     * )
     *
     * @Route("/dashboard/users_data")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserDashboardAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView');

        $month = null;
        if ($startDate && $endDate) {
            $month = $repo->countRegUsers($startDate.' 00:00:00', $endDate.' 23:59:59');
        }

        $result = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->countTotalUsers();

        $crmUrl = $this->container->getParameter('crm_api_url');
        $url = $crmUrl.'/commnue/admin/dashboard/users_auth?endDate='.$endDate.'&startDate='.$startDate;
        $ch = curl_init($url);

        $response = $this->callAPI($ch, 'GET');
        $response = json_decode($response, true);

        return new View([
            'total_users' => (int) $result['total'],
            'register_users' => $month,
            'auth_users' => $response['auth_users'],
        ]);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard/statistics")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getDashboardStatisticsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findBy(['platform' => AdminPosition::PLATFORM_COMMNUE]);

        $adminsSum = [];
        foreach ($positions as $position) {
            $admins = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->findBy(['positionId' => $position->getId()]);

            foreach ($admins as $admin) {
                array_push($adminsSum, $admin->getUserId());
            }
        }

        $adminsSum = array_unique($adminsSum);

        $communities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findBy(['isDeleted' => false]);

        $spaces = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->findBy([
                'isDeleted' => false,
                'visible' => false,
            ]);

        $authedCommunities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findBy([
                'isDeleted' => false,
                'commnueStatus' => RoomBuilding::CERTIFIED,
            ]);

        $bannedCommunities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findBy([
                'isDeleted' => false,
                'commnueStatus' => RoomBuilding::FREEZON,
            ]);

        $activities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->findBy([
                'isDeleted' => false,
                'status' => [
                    Event::STATUS_WAITING,
                    Event::STATUS_END,
                    Event::STATUS_ONGOING,
                    Event::STATUS_PREHEATING,
                    Event::STATUS_REGISTERING,
                ],
            ]);

        $registrationsSum = [];
        foreach ($activities as $activity) {
            $registrations = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Event\EventRegistration')
                ->findBy(['eventId' => $activity->getId()]);

            array_push($registrationsSum, $registrations);
        }

        $ongoingActivities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->findBy([
                'isDeleted' => false,
                'commnueVisible' => true,
                'status' => [
                    Event::STATUS_WAITING,
                    Event::STATUS_ONGOING,
                    Event::STATUS_PREHEATING,
                    Event::STATUS_REGISTERING,
                ],
            ]);

        $endedActivities = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\Event')
            ->findBy([
                'isDeleted' => false,
                'commnueVisible' => true,
                'status' => [
                    Event::STATUS_END,
                ],
            ]);

        $totalServicesCount = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\Service')
            ->getTotalServicesCount();

        $serviceOrderData = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getTotalServiceOrderData();

        return new View([
            'administrator' => [
                'current_positions' => count($positions),
                'current_administrators' => count($adminsSum),
            ],
            'community' => [
                'current_communities' => count($communities),
                'current_space' => count($spaces),
                'authed_companies' => count($authedCommunities),
                'banned_companies' => count($bannedCommunities),
            ],
            'activity' => [
                'current_activities' => count($activities),
                'registrations' => count($registrationsSum),
                'ongoing_activities' => count($ongoingActivities),
                'ended_activities' => count($endedActivities),
            ],
            'service' => [
                'services' => (int) $totalServicesCount,
                'servicesOrder_count' => (int) $serviceOrderData[2],
                'servicesOrder_amount' => $serviceOrderData[1] ? (int) $serviceOrderData[1] : 0
            ]
        ]);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard/leases_data")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getDashboardLeasesDataAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $currentContracts = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countContract();

        $effectiveStatus = array(
            Lease::LEASE_STATUS_PERFORMING,
            Lease::LEASE_STATUS_END,
            Lease::LEASE_STATUS_MATURED,
            Lease::LEASE_STATUS_TERMINATED,
            Lease::LEASE_STATUS_CLOSED,
        );

        $effectiveContracts = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countContract($effectiveStatus);

        $result = array(
            'current_contracts' => (int) $currentContracts['total_numbers'],
            'effective_contracts' => (int) $effectiveContracts['total_numbers'],
            'total_rent' => (float) $currentContracts['total_rent'],
        );

        return new View($result);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard/spaces_statistics")
     * @Method({"GET"})
     *
     * @return View
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getDashboardSpacesStatisticsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $meetingRooms = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->findBy(['type' => RoomTypes::TYPE_NAME_MEETING]);

        $officeRooms = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->findBy(['type' => RoomTypes::TYPE_NAME_OFFICE]);

        $otherRooms = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\Room')
            ->findBy(['type' => RoomTypes::TYPE_NAME_OTHERS]);

        $meetingOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersCount(RoomTypes::TYPE_NAME_MEETING);

        $officeOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersCount(RoomTypes::TYPE_NAME_OFFICE);

        $otherOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersCount(RoomTypes::TYPE_NAME_OTHERS);

        $meetingOrdersPriceSum = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersPriceSum(RoomTypes::TYPE_NAME_MEETING);

        $officeOrdersPriceSum = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersPriceSum(RoomTypes::TYPE_NAME_OFFICE);

        $othersOrdersPriceSum = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersPriceSum(RoomTypes::TYPE_NAME_OTHERS);

        return new View([
            'meeting_space_count' => count($meetingRooms),
            'meeting_order_count' => $meetingOrdersCount,
            'meeting_price_sum' => $meetingOrdersPriceSum,
            'office_space_count' => count($officeRooms),
            'office_order_count' => $officeOrdersCount,
            'office_price_sum' => $officeOrdersPriceSum,
            'others_space_count' => count($otherRooms),
            'others_order_count' => $otherOrdersCount,
            'others_price_sum' => $othersOrdersPriceSum,
        ]);
    }
}
