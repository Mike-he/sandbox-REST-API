<?php

namespace Sandbox\AdminApiBundle\Controller\Shop;

use Proxies\__CG__\Sandbox\ApiBundle\Entity\Admin\AdminType;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Form\Shop\ShopOrderRefundPatch;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Shop\ShopController;
use Sandbox\ApiBundle\Entity\Shop\Shop;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Admin ShopOrder Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminShopOrderController extends ShopController
{
    /**
     * patch shop order refund status.
     *
     * @param Request $request
     * @param $id
     *
     * @Method({"PATCH"})
     * @Route("/shop/orders/{id}/refund")
     *
     * @return View
     */
    public function patchShopOrderRefundAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_EDIT);

        $order = $this->getRepo('Shop\ShopOrder')->findOneBy(
            [
                'id' => $id,
                'status' => ShopOrder::STATUS_REFUNDED,
                'needToRefund' => true,
                'refunded' => false,
                'unoriginal' => false,
                'refundProcessed' => true,
                'payChannel' => ShopOrder::CHANNEL_UNIONPAY,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        // bind data
        $orderJson = $this->get('serializer')->serialize($order, 'json');
        $patch = new Patch($orderJson, $request->getContent());
        $orderJson = $patch->apply();

        $form = $this->createForm(new ShopOrderRefundPatch(), $order);
        $form->submit(json_decode($orderJson, true));

        $refunded = $order->isRefunded();
        $view = new View();

        if (!$refunded) {
            return $view;
        }

        $ssn = $order->getRefundSSN();

        if (is_null($ssn) || empty($ssn)) {
            return $this->customErrorView(
                400,
                self::REFUND_SSN_NOT_FOUND_CODE,
                self::REFUND_SSN_NOT_FOUND_MESSAGE
            );
        }

        $order->setNeedToRefund(false);
        $order->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $view;
    }

    /**
     * @Route("/shop/orders/{id}/refund")
     * @Method({"GET"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getShopOrderRefundLinkAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_EDIT);

        $order = $this->getRepo('Shop\ShopOrder')->findOneBy(
            [
                'id' => $id,
                'status' => ShopOrder::STATUS_REFUNDED,
                'needToRefund' => true,
                'refunded' => false,
                'unoriginal' => false,
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $price = $order->getRefundAmount();
        $link = $this->checkForRefund(
            $order,
            $price,
            ShopOrder::SHOP_MAP
        );

        $view = new View();
        $view->setData(['refund_link' => $link]);

        return $view;
    }

    /**
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/shop/orders/refund")
     * @Method({"GET"})
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
     */
    public function getRefundShopOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_EDIT);

        $orders = $this->getRepo('Shop\ShopOrder')->findBy(
            [
                'needToRefund' => true,
                'status' => ShopOrder::STATUS_REFUNDED,
                'unoriginal' => false,
                'refunded' => false,
            ],
            [
                'modificationDate' => 'ASC',
            ]
        );

        $orders = $this->get('serializer')->serialize(
            $orders,
            'json',
            SerializationContext::create()->setGroups(['admin_shop'])
        );
        $orders = json_decode($orders, true);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $orders,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by shop"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    requirements="{|unpaid|cancelled|paid|ready|completed|issue|waiting|refunded|}",
     *    strict=true,
     *    description="Filter by status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by payment date start"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by payment date end"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default="DESC",
     *    nullable=false,
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by order orderNumber, username"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many orders to return "
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
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by user id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by city id"
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
     *    name="refundStatus",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="refunded",
     *    strict=true,
     *    description="refund status filter for order "
     * )
     *
     * @Method({"GET"})
     * @Route("/shop/orders")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminShopOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_VIEW);

        $shopId = $paramFetcher->get('shop');
        $status = $paramFetcher->get('status');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $sort = $paramFetcher->get('sort');
        $search = $paramFetcher->get('search');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $user = $paramFetcher->get('user');
        $cityId = $paramFetcher->get('city');
        $buildingId = $paramFetcher->get('building');
        $refundStatus = $paramFetcher->get('refundStatus');

        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Shop\ShopOrder')
            ->getAdminShopOrdersForBackend(
                $shopId,
                $status,
                $start,
                $end,
                $sort,
                $search,
                $user,
                $cityId,
                $buildingId,
                $refundStatus,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Shop\ShopOrder')
            ->countAdminShopOrdersForBackend(
                $shopId,
                $status,
                $start,
                $end,
                $search,
                $user,
                $cityId,
                $buildingId,
                $refundStatus
            );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
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
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by shop"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by status"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by payment date start"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by payment date end"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default="DESC",
     *    nullable=false,
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search by order orderNumber, username"
     * )
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
     * @Method({"GET"})
     * @Route("/shop/orders/export")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminShopOrderExportAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();

        // check user permission
        $this->checkAdminOrderPermission($adminId, AdminPermissionMap::OP_LEVEL_VIEW);

        $shopId = $paramFetcher->get('shop');
        $status = $paramFetcher->get('status');
        $start = $paramFetcher->get('start');
        $end = $paramFetcher->get('end');
        $sort = $paramFetcher->get('sort');
        $search = $paramFetcher->get('search');
        $language = $paramFetcher->get('language');

        if (!is_null($status) && !empty($status)) {
            $status = explode(',', $status);
        }

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Shop\ShopOrder')
            ->getAdminShopOrdersForBackend(
                $shopId,
                $status,
                $start,
                $end,
                $sort,
                $search,
                null,
                null,
                null,
                null
            );

        return $this->getShopOrderExport($orders, $language);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Method({"GET"})
     * @Route("/shop/orders/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminShopOrderByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminOrderPermission($this->getAdminId(), AdminPermissionMap::OP_LEVEL_VIEW);

        $order = $this->getRepo('Shop\ShopOrder')->find($id);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_shop']));
        $view->setData($order);

        return $view;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     * @param int $adminId
     */
    private function checkAdminOrderPermission(
        $adminId,
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ORDER,
            $opLevel
        );
    }

    /**
     * authenticate with web browser cookie.
     */
    private function authenticateAdminCookie()
    {
        $cookie_name = self::ADMIN_COOKIE_NAME;
        if (!isset($_COOKIE[$cookie_name])) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $token = $_COOKIE[$cookie_name];
        $adminToken = $this->getRepo('Admin\AdminToken')->findOneByToken($token);
        if (is_null($adminToken)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        return $adminToken->getAdmin();
    }
}
