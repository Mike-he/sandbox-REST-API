<?php

namespace Sandbox\AdminApiBundle\Controller\Shop;

use Proxies\__CG__\Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
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
            ]
        );
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $order->setRefundProcessed(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $charge = $this->refundToPayChannel(
            $order,
            $order->getDiscountPrice(),
            ShopOrder::SHOP_MAP
        );

        $link = $this->getRefundLink($charge);

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
                'refunded' => false,
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

        $orders = $this->getRepo('Shop\ShopOrder')->getAdminShopOrdersForBackend(
            $shopId,
            $status,
            $start,
            $end,
            $sort,
            $search,
            $user
        );

        $orders = $this->get('serializer')->serialize(
            $orders,
            'json',
            SerializationContext::create()->setGroups(['admin_shop'])
        );
        $orders = json_decode($orders, true);

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

        $orders = $this->getRepo('Shop\ShopOrder')->getAdminShopOrdersForBackend(
            $shopId,
            $status,
            $start,
            $end,
            $sort,
            $search,
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

    /**
     * @param json $charge
     *
     * @return string
     */
    private function getRefundLink(
        $charge
    ) {
        $charge = json_decode($charge, true);

        if (!array_key_exists('failure_msg', $charge) || empty($charge['failure_msg'])) {
            return;
        }

        $link = $charge['failure_msg'];

        $linkArray = explode('https://', $link);
        $link = 'https://'.$linkArray[1];

        return $link;
    }
}
