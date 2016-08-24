<?php

namespace Sandbox\AdminApiBundle\Controller\DashBoard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;

/**
 * Class AdminDashBoardController.
 */
class AdminDashBoardController extends SandboxRestController
{
    /**
     * Get Total Number Of Users.
     *
     * @Route("/dashboard/users/total")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUsersTotalAction()
    {
        // check user permission
        $this->checkAdminDashboardPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView');
        $count = $repo->countTotalUsers();

        return new View(array(
            'total' => $count,
        ));
    }

    /**
     * Get Registration Number Of Users.
     *
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
     * @Route("/dashboard/users/reg")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUsersRegNumberAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminDashboardPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $now = new \DateTime('now');
        $yest = new \DateTime('now');
        $yest = $yest->modify('-1 day');

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView');
        $today = $repo->countRegUsers($now->format('Y-m-d 00:00:00'), $now->format('Y-m-d 23:59:59'));
        $yesterday = $repo->countRegUsers($yest->format('Y-m-d 00:00:00'), $yest->format('Y-m-d 23:59:59'));

        $month = 0;
        if ($startDate && $endDate) {
            $month = $repo->countRegUsers($startDate.' 00:00:00', $endDate.' 23:59:59');
        }

        return new View(array(
            'today' => $today,
            'yesterday' => $yesterday,
            'month' => $month,
        ));
    }

    /**
     * Check user permission.
     *
     * @param int $OpLevel
     */
    private function checkAdminDashboardPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_DASHBOARD,
            $OpLevel
        );
    }
}
