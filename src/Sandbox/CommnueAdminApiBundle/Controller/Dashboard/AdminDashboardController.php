<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Dashboard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
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
        ]);
    }
}
