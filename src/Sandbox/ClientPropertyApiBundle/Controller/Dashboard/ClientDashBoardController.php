<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Dashboard;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Reservation\Reservation;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ClientDashBoardController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard/statistical")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuildingsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];
        $adminId = $this->getAdminId();

        $em = $this->getDoctrine()->getManager();

        $grabStart = new \DateTime();
        $interval = new \DateInterval('P15D');
        $grabStart = $grabStart->sub($interval);

        $grabEnd = new \DateTime();

        $myLatestReservation = $em->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->countReservationByAdminId(
                $companyId,
                $adminId,
                Reservation::GRABED,
                $grabStart,
                $grabEnd
            );

        $waitingReservation = $em->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->countCompanyUngrabedReservation($companyId);

        $myBuildingIdsForOrder = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $adminId,
                array(
                    AdminPermission::KEY_SALES_BUILDING_ORDER,
                )
            );

        $unpaidOrders = $em->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countOrders(
                $myBuildingIdsForOrder,
                ProductOrder::STATUS_UNPAID
            );

        $myBuildingIdsForBills = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $adminId,
                array(
                    AdminPermission::KEY_SALES_BUILDING_LEASE_BILL,
                )
            );

        $leaseStatus = array(
            Lease::LEASE_STATUS_PERFORMING,
            Lease::LEASE_STATUS_TERMINATED,
            Lease::LEASE_STATUS_MATURED,
            Lease::LEASE_STATUS_END,
            Lease::LEASE_STATUS_CLOSED,
        );

        $unpaidBills = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillsForClientProperty(
                $leaseStatus,
                $myBuildingIdsForBills,
                LeaseBill::STATUS_UNPAID
            );

        $now = new \DateTime();
        $startDate = $now->format('Y-m-01 00:00:00');
        $endDate = $now->format('Y-m-t 23:59:59');
        $notPushedBills = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillsForClientProperty(
                $leaseStatus,
                $myBuildingIdsForBills,
                LeaseBill::STATUS_PENDING,
                $startDate,
                $endDate
            );

        $myBuildingIdsForLease = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $adminId,
                array(
                    AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                )
            );

        $expiringStart = new \DateTime();
        $expiringEnd = new \DateTime();
        $interval = new \DateInterval('P30D');
        $expiringEnd = $expiringEnd->add($interval);
        $expiringContract = $em->getRepository('SandboxApiBundle:Lease\Lease')
            ->countExpiringContract(
                $myBuildingIdsForLease,
                Lease::LEASE_STATUS_PERFORMING,
                $expiringStart,
                $expiringEnd
            );

        $result = [
            'my_latest_reservation' => $myLatestReservation,
            'waiting_reservation' => $waitingReservation,
            'unpaid_orders' => $unpaidOrders,
            'unpaid_bills' => $unpaidBills,
            'not_pushed_month_bills' => $notPushedBills,
            'expiring_contract' => $expiringContract,
        ];

        $view = new View();
        $view->setData($result);

        return $view;
    }
}
