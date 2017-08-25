<?php

namespace Sandbox\AdminApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\BeanConstants;
use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\User\UserBeanFlow;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdminUserBeanController extends UserProfileController
{
    /**
     * Get user's bean flows.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many banners to return per page"
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
     *    name="user",
     *    default=null,
     *    description="userId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Route("/user/bean/flows")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserBasicProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminUserPermission(AdminPermission::OP_LEVEL_VIEW);

        $userId = $paramFetcher->get('user');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $flows = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserBeanFlow')
            ->getFlows(
                $startDate,
                $endDate,
                $userId,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserBeanFlow')
            ->countFlows(
                $startDate,
                $endDate,
                $userId
            );

        foreach ($flows as $flow) {
            $source = $this->get('translator')->trans(BeanConstants::TRANS_USER_BEAN.$flow->getSource());

            if ($flow->getType() == UserBeanFlow::TYPE_CONSUME) {
                $DuibaOrder = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Duiba\DuibaOrder')
                    ->findOneBy(array('duibaOrderNum' => $flow->getTradeId()));

                $source = $DuibaOrder ? $source.'-'.$DuibaOrder->getDescription() : $source;
            }

            $flow->setSource($source);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $flows,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="end_date",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Route("/user/bean/flows/export")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
//        $admin = $this->authenticateAdminCookie();
//        $adminId = $admin->getId();

        // check user permission
//        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
//            $adminId,
//            [
//                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE],
//            ],
//            AdminPermission::OP_LEVEL_VIEW,
//            AdminPermission::PERMISSION_PLATFORM_OFFICIAL
//        );

        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        $flows = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserBeanFlow')
            ->getFlows(
                $startDate,
                $endDate
            );

        return $this->getFlowExport($flows);
    }

    /**
     * @param array $flows
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \PHPExcel_Exception
     */
    private function getFlowExport(
        $flows
    ) {
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setTitle('Sandbox Bean Flows');
        $excelBody = array();

        // set excel body
        foreach ($flows as $flow) {
            /** @var UserBeanFlow $flow */
            $source = $this->get('translator')->trans(BeanConstants::TRANS_USER_BEAN.$flow->getSource());

            if ($flow->getType() == UserBeanFlow::TYPE_CONSUME) {
                $DuibaOrder = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Duiba\DuibaOrder')
                    ->findOneBy(array('duibaOrderNum' => $flow->getTradeId()));

                $source = $DuibaOrder ? $source.'-'.$DuibaOrder->getDescription() : $source;
            }

            // set excel body
            $body = array(
                'creation_date' => $flow->getCreationDate()->format('Y-m-d'),
                'source' => $source,
                'add_amount' => $flow->getType() == UserBeanFlow::TYPE_ADD ? $flow->getChangeAmount() : '',
                'consume_amount' => $flow->getType() == UserBeanFlow::TYPE_CONSUME ? $flow->getChangeAmount() : '',
                'total' => $flow->getTotal(),
            );

            $excelBody[] = $body;
        }

        $headers = [
            '日期',
            '明细',
            '入账金额',
            '出账金额',
            '账户余额',
        ];

        //Fill data
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($headers, ' ', 'A1');
        $phpExcelObject->setActiveSheetIndex(0)->fromArray($excelBody, ' ', 'A2');

        $phpExcelObject->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);

        //set column dimension
        for ($col = ord('a'); $col <= ord('s'); ++$col) {
            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension(chr($col))->setAutoSize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('赤豆统计流水导表');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $date = new \DateTime('now');
        $stringDate = $date->format('Y-m-d H:i:s');

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'bean_flows_'.$stringDate.'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param $opLevel
     */
    private function checkAdminUserPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
            ],
            $opLevel
        );
    }
}
