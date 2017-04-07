<?php

namespace Sandbox\SalesApiBundle\Controller\MembershipCard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

/**
 * Admin MembershipCard Order Controller.
 */
class AdminMembershipCardOrderController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    default=null,
     *    nullable=true,
     *    array=true,
     *    description="payment channel"
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
     * @Method({"GET"})
     * @Route("/membership/cards/orders")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminMembershipCardOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $platform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $platform['sales_company_id'];

        $this->checkMembershipCardOrderPermission(AdminPermission::OP_LEVEL_VIEW);

        $channel = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getAdminOrders(
                $channel,
                $keyword,
                $keywordSearch,
                $limit,
                $offset,
                $companyId
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->countAdminOrders(
                $channel,
                $keyword,
                $keywordSearch,
                $companyId
            );

        $view = new View();
        //$view->setSerializationContext(SerializationContext::create()->setGroups(['admin_detail']));
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
     * @param Request $request
     * @param int     $id
     *
     * @Method({"GET"})
     * @Route("/membership/cards/orders/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminMembershipCardOrderByIdAction(
        Request $request,
        $id
    ) {
        $platform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $platform['sales_company_id'];

        $this->checkMembershipCardOrderPermission(AdminPermission::OP_LEVEL_VIEW);

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getAdminOrderById(
                $id,
                $companyId
            );

        $view = new View($order);
        //$view->setSerializationContext(SerializationContext::create()->setGroups(['admin_detail']));

        return $view;
    }

    /**
     * Check user permission.
     */
    private function checkMembershipCardOrderPermission(
        $OpLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MEMBERSHIP_CARD_ORDER],
            ],
            $OpLevel
        );
    }
}
