<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

class AdminLeaseExportController extends SalesRestController
{
    const KEY_CLUE = 'clue';
    const KEY_OFFER = 'offer';
    const KEY_LEASE = 'lease';
    const KEY_BILL = 'bill';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    default="zh",
     *    nullable=true,
     *    requirements="(zh|en)",
     *    strict=true,
     *    description="export language"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="applicant, room, number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="rent_filter",
     *    default=null,
     *    nullable=true,
     *    description="rent time filter keywords"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default="",
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Route("/lease/export/clues")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportCluesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_BUILDING_LEASE_CLUE,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $language = $paramFetcher->get('language');
        $buildingId = $paramFetcher->get('building');
        $status = $paramFetcher->get('status');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        $createStart = new \DateTime($createStart);
        $createStart->setTime(0, 0, 0);

        $createEnd = new \DateTime($createEnd);
        $createEnd->setTime(23, 59, 59);

        $clues = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->findClues(
                $data['building_ids'],
                $buildingId,
                $status,
                $keyword,
                $keywordSearch,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate
            );

        $createDate = array();
        foreach ($clues as $clue) {
            $createDate[] = $clue->getCreationDate()->format('Ymd');
        }

        $min = '';
        $max = '';
        if ($createDate) {
            $minPos = array_search(min($createDate), $createDate);
            $min = $createDate[$minPos];

            $maxPos = array_search(max($createDate), $createDate);
            $max = $createDate[$maxPos];
        }

        return $this->get('sandbox_api.export')->exportExcel(
            $clues,
            GenericList::OBJECT_LEASE_CLUE,
            $data['user_id'],
            $language,
            $min,
            $max
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    default="zh",
     *    nullable=true,
     *    requirements="(zh|en)",
     *    strict=true,
     *    description="export language"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="applicant, room, number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="rent_filter",
     *    default=null,
     *    nullable=true,
     *    description="rent time filter keywords"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default="",
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Route("/lease/export/offers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportOffersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_BUILDING_LEASE_OFFER,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $language = $paramFetcher->get('language');
        $buildingId = $paramFetcher->get('building');
        $status = $paramFetcher->get('status');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        $offers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseOffer')
            ->findOffers(
                $data['building_ids'],
                $buildingId,
                $status,
                $keyword,
                $keywordSearch,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate
            );

        $createDate = array();
        foreach ($offers as $offer) {
            $createDate[] = $offer->getCreationDate()->format('Ymd');
        }

        $min = '';
        $max = '';
        if ($createDate) {
            $minPos = array_search(min($createDate), $createDate);
            $min = $createDate[$minPos];

            $maxPos = array_search(max($createDate), $createDate);
            $max = $createDate[$maxPos];
        }

        return $this->get('sandbox_api.export')->exportExcel(
            $offers,
            GenericList::OBJECT_LEASE_OFFER,
            $data['user_id'],
            $language,
            $min,
            $max
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    default="zh",
     *    nullable=true,
     *    requirements="(zh|en)",
     *    strict=true,
     *    description="export language"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="applicant, room, number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="last_week, last_month"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="rent_filter",
     *    default=null,
     *    nullable=true,
     *    description="rent time filter keywords"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="room",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by room id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="user",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by user id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Route("/lease/export/leases")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportLeasesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $language = $paramFetcher->get('language');
        $status = $paramFetcher->get('status');
        $roomId = $paramFetcher->get('room');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $createRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');
        $buildingId = $paramFetcher->get('building');

        $myBuildingIds = $buildingId ? array((int) $buildingId) : $data['building_ids'];

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findLeases(
                $myBuildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $rentFilter,
                $startDate,
                $endDate,
                $data['company_id'],
                $roomId
            );

        $createDate = array();
        foreach ($leases as $lease) {
            $createDate[] = $lease->getCreationDate()->format('Ymd');
        }

        $min = '';
        $max = '';
        if ($createDate) {
            $minPos = array_search(min($createDate), $createDate);
            $min = $createDate[$minPos];

            $maxPos = array_search(max($createDate), $createDate);
            $max = $createDate[$maxPos];
        }

        return $this->get('sandbox_api.export')->exportExcel(
            $leases,
            GenericList::OBJECT_LEASE,
            $data['user_id'],
            $language,
            $min,
            $max
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="language",
     *    default="zh",
     *    nullable=true,
     *    requirements="(zh|en)",
     *    strict=true,
     *    description="export language"
     * )
     *
     * * @Annotations\QueryParam(
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="pay channel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="send_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="send start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="send_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="send end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pay_start_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="pay_end_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default="",
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Route("/lease/export/bills")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_BUILDING_LEASE_BILL,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $language = $paramFetcher->get('language');
        $channels = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $sendStart = $paramFetcher->get('send_start');
        $sendEnd = $paramFetcher->get('send_end');
        $payStartDate = $paramFetcher->get('pay_start_date');
        $payEndDate = $paramFetcher->get('pay_end_date');
        $status = $paramFetcher->get('status');
        $building = $paramFetcher->get('building');

        $leaseStatus = array(
            Lease::LEASE_STATUS_PERFORMING,
            Lease::LEASE_STATUS_TERMINATED,
            Lease::LEASE_STATUS_MATURED,
            Lease::LEASE_STATUS_END,
            Lease::LEASE_STATUS_CLOSED,
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsForSales(
                $data['building_ids'],
                $building,
                $status,
                $channels,
                $keyword,
                $keywordSearch,
                $sendStart,
                $sendEnd,
                $payStartDate,
                $payEndDate,
                $leaseStatus
            );

        $sendDate = array();
        foreach ($bills as $bill) {
            if ($bill->getSendDate()) {
                $sendDate[] = $bill->getSendDate()->format('Ymd');
            }
        }

        $min = '';
        $max = '';
        if ($sendDate) {
            $minPos = array_search(min($sendDate), $sendDate);
            $min = $sendDate[$minPos];

            $maxPos = array_search(max($sendDate), $sendDate);
            $max = $sendDate[$maxPos];
        }

        return $this->get('sandbox_api.export')->exportExcel(
            $bills,
            GenericList::OBJECT_LEASE_BILL,
            $data['user_id'],
            $language,
            $min,
            $max
        );
    }
}
