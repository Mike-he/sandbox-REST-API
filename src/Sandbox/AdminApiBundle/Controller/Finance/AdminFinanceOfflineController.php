<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

class AdminFinanceOfflineController extends SandboxRestController
{
    /**
     * Get offline Bills lists.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true,
     *    description="status"
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
     *    name="amount_start",
     *    default=null,
     *    nullable=true,
     *    description="amount start query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_end",
     *    default=null,
     *    nullable=true,
     *    description="amount end query"
     * )
     *
     * @Route("/finance/offline/bills")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBillsListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminFinanceOfflinePermission(AdminPermission::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $sendStart = $paramFetcher->get('send_start');
        $sendEnd = $paramFetcher->get('send_end');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBillsByCompany(
                null,
                LeaseBill::CHANNEL_OFFLINE,
                $status,
                $keyword,
                $keywordSearch,
                $sendStart,
                $sendEnd,
                $amountStart,
                $amountEnd
            );

        $bills = $this->get('serializer')->serialize(
            $bills,
            'json',
            SerializationContext::create()->setGroups(['lease_bill'])
        );
        $bills = json_decode($bills, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $bills,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * get Offline Finance Order.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Order Status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pay_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment start. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="pay_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="filter for payment end. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_start",
     *    default=null,
     *    nullable=true,
     *    description="amount start query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="amount_end",
     *    default=null,
     *    nullable=true,
     *    description="amount end query"
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
     * @Route("/finance/offline/orders")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getOrdersForFinanceAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminFinanceOfflinePermission(AdminPermission::OP_LEVEL_VIEW);

        //filters
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $payStart = $paramFetcher->get('pay_start');
        $payEnd = $paramFetcher->get('pay_end');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $status = $paramFetcher->get('status');
        $amountStart = $paramFetcher->get('amount_start');
        $amountEnd = $paramFetcher->get('amount_end');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOrdersForFinance(
                ProductOrder::CHANNEL_OFFLINE,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $status,
                $amountStart,
                $amountEnd,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countOrdersForFinance(
                ProductOrder::CHANNEL_OFFLINE,
                $payStart,
                $payEnd,
                $keyword,
                $keywordSearch,
                $status,
                $amountStart,
                $amountEnd
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_detail']));
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $orders,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param $opLevel
     */
    private function checkAdminFinanceOfflinePermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_TRANSFER_CONFIRM],
            ],
            $opLevel
        );
    }
}
