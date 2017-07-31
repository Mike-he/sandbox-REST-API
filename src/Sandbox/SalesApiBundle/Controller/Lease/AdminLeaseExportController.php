<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Entity\Lease\LeaseOffer;
use Sandbox\ApiBundle\Entity\Lease\LeaseRentTypes;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
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
        $data = $this->checkPermission(AdminPermission::KEY_SALES_PLATFORM_LEASE_CLUE);

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

        $clues = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
            ->findClues(
                $data['company_id'],
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

        $lists = $this->getGenericLists(GenericList::OBJECT_LEASE_CLUE, $data['user_id']);

        return $this->exportExcel(
            $clues,
            $lists,
            $language,
            self::KEY_CLUE,
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
        $data = $this->checkPermission(AdminPermission::KEY_SALES_PLATFORM_LEASE_CLUE);

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
                $data['company_id'],
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

        $lists = $this->getGenericLists(GenericList::OBJECT_LEASE_OFFER, $data['user_id']);

        return $this->exportExcel(
            $offers,
            $lists,
            $language,
            self::KEY_OFFER,
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
        $data = $this->checkPermission(AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE);

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
        $myBuildingIds = $buildingId ? array((int) $buildingId) : array();

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

        $lists = $this->getGenericLists(GenericList::OBJECT_LEASE, $data['user_id']);

        return $this->exportExcel(
            $leases,
            $lists,
            $language,
            self::KEY_LEASE,
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
     * @Route("/lease/export/bills")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->checkPermission(AdminPermission::KEY_SALES_PLATFORM_LEASE_BILL);

        $language = $paramFetcher->get('language');
        $channel = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $sendStart = $paramFetcher->get('send_start');
        $sendEnd = $paramFetcher->get('send_end');
        $payStartDate = $paramFetcher->get('pay_start_date');
        $payEndDate = $paramFetcher->get('pay_end_date');
        $status = $paramFetcher->get('status');

        if ($channel == LeaseBill::CHANNEL_SANDBOX) {
            $channels = array(
                LeaseBill::CHANNEL_ALIPAY,
                LeaseBill::CHANNEL_WECHAT,
                LeaseBill::CHANNEL_OFFLINE,
                LeaseBill::CHANNEL_UNIONPAY,
            );
        } else {
            $channels = $channel ? [$channel] : [];
        }

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
                $data['company_id'],
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

        $lists = $this->getGenericLists(GenericList::OBJECT_LEASE_BILL, $data['user_id']);

        return $this->exportExcel(
            $bills,
            $lists,
            $language,
            self::KEY_BILL,
            $min,
            $max
        );
    }

    /**
     * @param $permission
     *
     * @return array
     */
    private function checkPermission(
        $permission
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();
        $token = $_COOKIE[self::ADMIN_COOKIE_NAME];

        $userToken = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy([
                'userId' => $adminId,
                'token' => $token,
            ]);
        $this->throwNotFoundIfNull($userToken, self::NOT_FOUND_MESSAGE);

        $adminPlatform = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
            ->findOneBy(array(
                'userId' => $adminId,
                'clientId' => $userToken->getClientId(),
            ));
        if (is_null($adminPlatform)) {
            throw new PreconditionFailedHttpException(self::PRECONDITION_NOT_SET);
        }

        $companyId = $adminPlatform->getSalesCompanyId();

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => $permission],
            ],
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_SALES,
            $companyId
        );

        $result = array(
            'company_id' => $companyId,
            'user_id' => $adminId,
        );

        return $result;
    }

    /**
     * @param $object
     * @param $adminId
     *
     * @return array
     */
    private function getGenericLists(
        $object,
        $adminId
    ) {
        $genericUserLists = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:GenericList\GenericUserList')
            ->findBy(
                array(
                    'object' => $object,
                    'userId' => $adminId,
                )
            );

        $lists = array();
        if ($genericUserLists) {
            foreach ($genericUserLists as $genericUserList) {
                $lists[$genericUserList->getList()->getColumn()] = $genericUserList->getList()->getName();
            }
        } else {
            $genericLists = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:GenericList\GenericList')
                ->findBy(
                    array(
                        'object' => $object,
                        'platform' => AdminPermission::PERMISSION_PLATFORM_SALES,
                        'default' => true,
                    )
                );
            foreach ($genericLists as $genericList) {
                $lists[$genericList->getColumn()] = $genericList->getName();
            }
        }

        return $lists;
    }

    /**
     * @param $data
     * @param $lists
     * @param $language
     * @param $key
     * @param $min
     * @param $max
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportExcel(
        $data,
        $lists,
        $language,
        $key,
        $min,
        $max
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Excel');

        $headers = [];
        foreach ($lists as $list) {
            $headers[] = $list;
        }

        switch ($key) {
            case self::KEY_CLUE:
                $excelBody = $this->getExcelClueData($data, $lists, $language);
                $fileName = '线索'.$min.' - '.$max;
                break;
            case self::KEY_OFFER:
                $excelBody = $this->getExcelOfferData($data, $lists, $language);
                $fileName = '报价'.$min.' - '.$max;
                break;
            case self::KEY_LEASE:
                $excelBody = $this->getExcelLeaseData($data, $lists, $language);
                $fileName = '合同'.$min.' - '.$max;
                break;
            case self::KEY_BILL:
                $excelBody = $this->getExcelBillData($data, $lists, $language);
                $fileName = '账单'.$min.' - '.$max;
                break;
            default:
                $excelBody = array();
                $fileName = null;
        }

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('s'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('导表');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $filename = $fileName.'.xls';

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }

    /**
     * @param LeaseClue $clues
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelClueData(
        $clues,
        $lists,
        $language
    ) {
        $status = array(
            LeaseClue::LEASE_CLUE_STATUS_CLUE => '新线索',
            LeaseClue::LEASE_CLUE_STATUS_OFFER => '转为报价',
            LeaseClue::LEASE_CLUE_STATUS_CONTRACT => '转为合同',
            LeaseClue::LEASE_CLUE_STATUS_CLOSED => '已关闭',
        );

        $excelBody = array();
        foreach ($clues as $clue) {
            /** @var LeaseClue $clue */
            $appointmentName = null;
            if ($clue->getProductAppointmentId()) {
                $appointment = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\ProductAppointment')
                    ->find($clue->getProductAppointmentId());

                $appointmentName = $appointment ? $appointment->getApplicantName() : null;
            }

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($clue->getLesseeCustomer());

            $roomData = $this->getRoomData($clue->getProductId(), $language);

            $clueList = array(
                'serial_number' => $clue->getSerialNumber(),
                'room_name' => $roomData['room_name'],
                'room_type_tag' => $roomData['room_type_tag'],
                'lessee_name' => $clue->getLesseeName(),
                'lessee_address' => $clue->getLesseeAddress(),
                'lessee_customer' => $customer->getName(),
                'lessee_email' => $clue->getLesseeEmail(),
                'lessee_phone' => $clue->getLesseePhone(),
                'start_date' => $clue->getStartDate() ? $clue->getStartDate()->format('Y-m-d H:i:s') : '',
                'cycle' => $clue->getCycle() ? $clue->getCycle().'个月' : '',
                'monthly_rent' => $clue->getMonthlyRent() ? $clue->getMonthlyRent().'元/月起' : '',
                'number' => $clue->getNumber(),
                'creation_date' => $clue->getCreationDate()->format('Y-m-d H:i:s'),
                'status' => $status[$clue->getStatus()],
                'total_rent' => $clue->getMonthlyRent() * $clue->getNumber(),
                'appointment_user' => $appointmentName,
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $clueList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param LeaseOffer $offers
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelOfferData(
        $offers,
        $lists,
        $language
    ) {
        $status = array(
            LeaseOffer::LEASE_OFFER_STATUS_OFFER => '报价中',
            LeaseOffer::LEASE_OFFER_STATUS_CONTRACT => '转为合同',
            LeaseOffer::LEASE_OFFER_STATUS_CLOSED => '已关闭',
        );

        $excelBody = array();
        foreach ($offers as $offer) {
            /** @var LeaseOffer $offer */
            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($offer->getLesseeCustomer());

            $enterpriseName = null;
            if ($offer->getLesseeEnterprise()) {
                $enterprise = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
                    ->find($offer->getLesseeEnterprise());

                $enterpriseName = $enterprise ? $enterprise->getName() : null;
            }

            $startDate = $offer->getStartDate() ? $offer->getStartDate()->format('Y-m-d H:i:s') : '';
            $endDate = $offer->getEndDate() ? $offer->getEndDate()->format('Y-m-d H:i:s') : '';

            $leaseRentTypes = $offer->getLeaseRentTypes();
            $taxTypes = array();
            foreach ($leaseRentTypes as $leaseRentType) {
                if ($leaseRentType->getType() == LeaseRentTypes::RENT_TYPE_TAX) {
                    $taxTypes[] = $leaseRentType->getName();
                }
            }

            $taxTypes = implode(',', $taxTypes);

            $roomData = $this->getRoomData($offer->getProductId(), $language);

            $offerList = array(
                'serial_number' => $offer->getSerialNumber(),
                'room_name' => $roomData['room_name'],
                'room_type_tag' => $roomData['room_type_tag'],
                'lessee_type' => $offer->getLesseeType() == LeaseOffer::LEASE_OFFER_LESSEE_TYPE_PERSONAL ? '个人承租' : '企业承租',
                'lessee_enterprise' => $enterpriseName,
                'lessee_customer' => $customer->getName(),
                'start_date' => $startDate.' - '.$endDate,
                'monthly_rent' => $offer->getMonthlyRent() ? $offer->getMonthlyRent().'元/月起' : '',
                'deposit' => $offer->getDeposit() ? $offer->getDeposit().'元' : '',
                'lease_rent_types' => $taxTypes,
                'creation_date' => $offer->getCreationDate()->format('Y-m-d H:i:s'),
                'status' => $status[$offer->getStatus()],
                'total_rent' => $offer->getTotalRent(),
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $offerList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param Lease $leases
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelLeaseData(
        $leases,
        $lists,
        $language
    ) {
        $status = array(
            Lease::LEASE_STATUS_DRAFTING => '未生效',
            Lease::LEASE_STATUS_PERFORMING => '履行中',
            Lease::LEASE_STATUS_TERMINATED => '已终止',
            Lease::LEASE_STATUS_MATURED => '已到期',
            Lease::LEASE_STATUS_END => '已结束',
            Lease::LEASE_STATUS_CLOSED => '已作废',
        );

        $excelBody = array();
        foreach ($leases as $lease) {
            /** @var Lease $lease */
            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($lease->getLesseeCustomer());

            $enterpriseName = null;
            if ($lease->getLesseeEnterprise()) {
                $enterprise = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
                    ->find($lease->getLesseeEnterprise());

                $enterpriseName = $enterprise ? $enterprise->getName() : null;
            }

            $startDate = $lease->getStartDate() ? $lease->getStartDate()->format('Y-m-d H:i:s') : '';
            $endDate = $lease->getEndDate() ? $lease->getEndDate()->format('Y-m-d H:i:s') : '';

            $leaseRentTypes = $lease->getLeaseRentTypes();
            $taxTypes = array();
            foreach ($leaseRentTypes as $leaseRentType) {
                if ($leaseRentType->getType() == LeaseRentTypes::RENT_TYPE_TAX) {
                    $taxTypes[] = $leaseRentType->getName();
                }
            }

            $taxTypes = implode(',', $taxTypes);

            $roomData = $this->getRoomData($lease->getProductId(), $language);

            $leaseBillsCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_LEASE
                );

            $otherBillsCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_OTHER
                );

            $leaseList = array(
                'serial_number' => $lease->getSerialNumber(),
                'room_name' => $roomData['room_name'],
                'room_type_tag' => $roomData['room_type_tag'],
                'lessee_type' => $lease->getLesseeType() == Lease::LEASE_LESSEE_TYPE_PERSONAL ? '个人承租' : '企业承租',
                'lessee_enterprise' => $enterpriseName,
                'lessee_customer' => $customer ? $customer->getName() : '',
                'start_date' => $startDate.' - '.$endDate,
                'monthly_rent' => $lease->getMonthlyRent() ? $lease->getMonthlyRent().'元/月起' : '',
                'deposit' => $lease->getDeposit() ? $lease->getDeposit().'元' : '',
                'lease_rent_types' => $taxTypes,
                'creation_date' => $lease->getCreationDate()->format('Y-m-d H:i:s'),
                'status' => $status[$lease->getStatus()],
                'total_rent' => $lease->getTotalRent(),
                'lease_bill' => $leaseBillsCount,
                'other_bill' => $otherBillsCount,
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $leaseList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param LeaseBill $bills
     * @param $lists
     * @param $language
     *
     * @return array
     */
    private function getExcelBillData(
        $bills,
        $lists,
        $language
    ) {
        $status = array(
            LeaseBill::STATUS_PENDING => '未推送',
            LeaseBill::STATUS_UNPAID => '未付款',
            LeaseBill::STATUS_PAID => '已付款',
            LeaseBill::STATUS_VERIFY => '待确认',
            LeaseBill::STATUS_CANCELLED => '已取消',
        );

        $excelBody = array();
        foreach ($bills as $bill) {
            /** @var LeaseBill $bill */
            $company = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
                ->find($bill->getLease()->getCompanyId());

            $payments = $this->getDoctrine()->getRepository('SandboxApiBundle:Payment\Payment')->findAll();
            $payChannel = array();
            foreach ($payments as $payment) {
                $payChannel[$payment->getChannel()] = $payment->getName();
            }

            $drawee = null;
            if ($bill->getCustomerId()) {
                $customer = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\UserCustomer')
                    ->find($bill->getCustomerId());

                $drawee = $customer ? $customer->getName() : '';
            }

            $startDate = $bill->getStartDate() ? $bill->getStartDate()->format('Y-m-d H:i:s') : '';
            $endDate = $bill->getEndDate() ? $bill->getEndDate()->format('Y-m-d H:i:s') : '';

            $invoice = false;
            $leaseRentTypes = $bill->getLease()->getLeaseRentTypes();
            foreach ($leaseRentTypes as $leaseRentType) {
                if ($leaseRentType->getType() == LeaseRentTypes::RENT_TYPE_TAX) {
                    $invoice = true;
                }
            }

            $billList = array(
                'serial_number' => $bill->getSerialNumber(),
                'lease_serial_number' => $bill->getLease()->getSerialNumber(),
                'drawer' => $bill->isSalesInvoice() ? $company->getName().'开票' : '创合开票',
                'name' => $bill->getName(),
                'description' => $bill->getDescription(),
                'amount' => $bill->getAmount(),
                'invoice' => $invoice ? '包含发票' : '不包含发票',
                'start_date' => $startDate.' - '.$endDate,
                'drawee' => $drawee,
                'order_method' => $bill->getOrderMethod() == LeaseBill::ORDER_METHOD_BACKEND ? '后台推送' : '自动推送',
                'pay_channel' => $bill->getPayChannel() ? $payChannel[$bill->getPayChannel()] : '',
                'send_date' => $bill->getSendDate() ? $bill->getSendDate()->format('Y-m-d H:i:s') : '',
                'status' => $status[$bill->getStatus()],
                'revised_amount' => $bill->getRevisedAmount() ? $bill->getRevisedAmount().'元' : '',
                'remark' => $bill->getRemark(),
            );

            $body = array();
            foreach ($lists as $key => $value) {
                $body[] = $billList[$key];
            }

            $excelBody[] = $body;
        }

        return $excelBody;
    }

    /**
     * @param $productId
     * @param $language
     *
     * @return array
     */
    private function getRoomData(
        $productId,
        $language
    ) {
        $roomName = null;
        $roomTypeTag = null;
        if ($productId) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($productId);

            if ($product) {
                $roomName = $product->getRoom()->getName();
                $tag = $product->getRoom()->getTypeTag();

                $roomTypeTag = $this->get('translator')->trans(
                    ProductOrderExport::TRANS_PREFIX.$tag,
                    array(),
                    null,
                    $language
                );
            }
        }

        $result = array(
            'room_name' => $roomName,
            'room_type_tag' => $roomTypeTag,
        );

        return $result;
    }
}
