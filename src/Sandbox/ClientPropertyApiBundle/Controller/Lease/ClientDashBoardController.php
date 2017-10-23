<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Lease;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ClientDashBoardController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/dashboard")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getStatisticalAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminId = $this->getAdminId();

        $BillsCount = $this->countBills($adminId);

        $expiringContract = $this->countExpiringContract($adminId);

        $newClues = $this->countClues($adminId);

        $result = [
            'pending_bills' => $BillsCount['pending_bills'],
            'unpaid_bills' => $BillsCount['unpaid_bills'],
            'expiring_contract' => $expiringContract,
            'new_clues' => $newClues,
        ];

        $view = new View();
        $view->setData($result);

        return $view;
    }

    /**
     * @param $adminId
     *
     * @return array
     */
    private function countBills(
        $adminId
    ) {
        $myBuildingIds = $this->get('sandbox_api.admin_permission_check_service')
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

        $unpaidBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillsForClientProperty(
                $leaseStatus,
                $myBuildingIds,
                LeaseBill::STATUS_UNPAID
            );

        $pendingBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBillsForClientProperty(
                $leaseStatus,
                $myBuildingIds,
                LeaseBill::STATUS_PENDING
            );

        $result = [
            'unpaid_bills' => $unpaidBills,
            'pending_bills' => $pendingBills,
        ];

        return $result;
    }

    /**
     * @param $adminId
     *
     * @return int
     */
    private function countExpiringContract($adminId)
    {
        $myBuildingIds = $this->get('sandbox_api.admin_permission_check_service')
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

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countExpiringContract(
                $myBuildingIds,
                Lease::LEASE_STATUS_PERFORMING,
                $expiringStart,
                $expiringEnd
            );

        return $count;
    }

    private function countClues(
        $adminId
    ) {
        $myBuildingIds = $this->get('sandbox_api.admin_permission_check_service')
            ->getMySalesBuildingIds(
                $adminId,
                array(
                    AdminPermission::KEY_SALES_BUILDING_LEASE_CLUE,
                )
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->countClues(
                $myBuildingIds,
                null,
                LeaseClue::LEASE_CLUE_STATUS_CLUE
            );

        return $count;
    }
}
