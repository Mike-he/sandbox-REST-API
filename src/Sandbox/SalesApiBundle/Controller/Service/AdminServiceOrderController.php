<?php

namespace Sandbox\SalesApiBundle\Controller\Service;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Finance\FinanceSalesWalletFlow;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ServiceOrder;
use Sandbox\ApiBundle\Traits\HandleServiceDataTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

class AdminServiceOrderController extends SalesRestController
{
    use HandleServiceDataTrait;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/service/orders")
     * @Method({"GET"})
     *
     * @return View
     *
     *  @throws \Doctrine\ORM\Query\QueryException
     */
    public function getOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getServiceOrders(
                $companyId,
                $keyword,
                $keywordSearch,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->countServiceOrders(
                $companyId,
                $keyword,
                $keywordSearch
            );

        foreach ($orders as $order) {
            /**
             * @var ServiceOrder
             */
            $service = $order->getService();
            $this->handleServicesData($service);
        }

        $view = new View();
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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="language",
     *    array=false,
     *    default=null,
     *    nullable=true
     * )
     *
     * @Route("/service/export/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function exportOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_SERVICE_ORDER,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $language = $paramFetcher->get('language');

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');


        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->getServiceOrders(
                $data['company_id'],
                $keyword,
                $keywordSearch
            );

        return $this->get('sandbox_api.export')->exportExcel(
            $orders,
            GenericList::OBJECT_SERVICE_ORDER,
            $data['user_id'],
            $language
        );

    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/service/orders/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOrderByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->findOneBy(array(
                'id' => $id,
                'companyId' => $companyId,
            ));
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $company = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($companyId);
        $order->setCompanyName($company->getName());

        $service = $order->getService();
        $this->handleServicesData($service);

        $view = new View($order);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
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
     * @Route("/service/{id}/orders/purchase")
     * @Method({"GET"})
     *
     * @return mixed
     */
    public function getPurchaseUserAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->findPurchaseOrders(
                $id,
                $companyId,
                $limit,
                $offset
            );
        $this->throwNotFoundIfNull($orders, self::NOT_FOUND_MESSAGE);

        $count = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceOrder')
        ->getServicePurchaseCount($id);

        $result = [];
        foreach ($orders as $order) {
            $result[] = $this->handlePurchaseInfo($order);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $result,
                'total_count' => (int) $count,
            )
        );

        return  $view;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/service/orders/form/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getPurchaseDetailByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_VIEW);

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->find($id);

        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);
        $result = array();

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($order->getCustomerId());
        $purchaseForm = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ServicePurchaseForm')
            ->findBy(['order' => $order]);

        $result['user'] = $user;
        $result['form'] = $purchaseForm;

        return new View($result);
    }

    /**
     * @param $id
     *
     * @Route("/service/orders/{id}/completed")
     * @Method({"POST"})
     *
     * @return View
     */
    public function changeServiceOrdersStatusAction(
        $id
    ) {
        // check user permission
        $this->checkPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $adminPlatform['sales_company_id'];

        $order = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\ServiceOrder')
            ->findOneBy(array(
                'id' => $id,
                'status' => ServiceOrder::STATUS_PAID,
                'companyId' => $companyId,
            ));

        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $order->setStatus(ServiceOrder::STATUS_COMPLETED);

        $wallet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
            ->findOneBy(['companyId' => $companyId]);

        if (!is_null($wallet)) {
            $totalAmount = $wallet->getTotalAmount();
            $withdrawAmount = $wallet->getWithdrawableAmount();

            $price = $order->getPrice();
            $currentWithdrawAmount = $withdrawAmount + $price;

            $wallet->setTotalAmount($totalAmount + $price);
            $wallet->setWithdrawableAmount($currentWithdrawAmount);

            $this->container->get('sandbox_api.sales_wallet')
                ->generateSalesWalletFlows(
                    FinanceSalesWalletFlow::REALTIME_SERVICE_ORDERS_AMOUNT,
                    "+$price",
                    $companyId,
                    $order->getOrderNumber(),
                    $currentWithdrawAmount
                );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param ServiceOrder $order
     *
     * @return array
     */
    private function handlePurchaseInfo(
        $order
    ) {
        $data = [];
        $user = $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($order->getCustomerId());
        $data['id'] = $order->getId();
        $data['user_id'] = $user->getId();
        $data['user_name'] = $user->getName();
        $data['user_sex'] = $user->getSex();
        $data['email'] = $user->getEmail();
        $data['payment_date'] = $order->getPaymentDate();
        $data['status'] = $order->getStatus();

        return $data;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_SERVICE_ORDER,
                ),
            ),
            $opLevel
        );
    }
}
