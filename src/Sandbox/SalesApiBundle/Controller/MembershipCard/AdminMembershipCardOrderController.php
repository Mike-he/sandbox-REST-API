<?php

namespace Sandbox\SalesApiBundle\Controller\MembershipCard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Traits\FinanceSalesExportTraits;

/**
 * Admin MembershipCard Order Controller.
 */
class AdminMembershipCardOrderController extends SalesRestController
{
    use FinanceSalesExportTraits;

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
     * @Route("/membership/cards/{id}/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembershipCardOrdersByIdAction(
        Request $request,
        $id,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkMembershipCardOrderPermission(AdminPermission::OP_LEVEL_VIEW);

        $membershipCard = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->find($id);
        $this->throwNotFoundIfNull($membershipCard, self::NOT_FOUND_MESSAGE);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getAdminOrders(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $limit,
                $offset,
                null,
                $id
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->countAdminOrders(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $id
            );

        foreach ($orders as $order) {
            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['userId' => $order->getUser()]);
            if (!is_null($profile)) {
                $order->setUserInfo(['username' => $profile->getName()]);
            }
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
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="create_date_range"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="create_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
     * )
     *
     * @Annotations\QueryParam(
     *     name="user",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     requirements="\d+",
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort_column",
     *    default=null,
     *    nullable=true,
     *    description="sort column"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    default=null,
     *    nullable=true,
     *    description="sort direction"
     * )
     *
     * @Method({"GET"})
     * @Route("/membership/cards/orders/list")
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
        $buildingId = $paramFetcher->get('building');
        $createDateRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $userId = $paramFetcher->get('user');

        //sort
        $sortColumn = $paramFetcher->get('sort_column');
        $direction = $paramFetcher->get('direction');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getAdminOrders(
                $channel,
                $keyword,
                $keywordSearch,
                $buildingId,
                $createDateRange,
                $createStart,
                $createEnd,
                $limit,
                $offset,
                $companyId,
                null,
                $userId,
                $sortColumn,
                $direction
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->countAdminOrders(
                $channel,
                $keyword,
                $keywordSearch,
                $buildingId,
                $createDateRange,
                $createStart,
                $createEnd,
                $companyId,
                null,
                $userId
            );

        foreach ($orders as $order) {
            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['userId' => $order->getUser()]);
            if (!is_null($profile)) {
                $order->setUserInfo(['username' => $profile->getName()]);
            }
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
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="create_date_range"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="create_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
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
     * @Route("/membership/cards/orders/export")
     *
     * @return View
     */
    public function getSalesMembershipOrderExportAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = $this->get('sandbox_api.admin_permission_check_service')
            ->checkPermissionByCookie(
                AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

        $language = $paramFetcher->get('language');
        $channel = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $buildingId = $paramFetcher->get('building');
        $createDateRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getAdminOrders(
                $channel,
                $keyword,
                $keywordSearch,
                $buildingId,
                $createDateRange,
                $createStart,
                $createEnd,
                null,
                null,
                $data['company_id']
            );

        return $this->get('sandbox_api.export')->exportExcel(
            $orders,
            GenericList::OBJECT_MEMBERSHIP_ORDER,
            $data['user_id'],
            $language
        );
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

        if (is_null($order)) {
            return new View();
        }

        $card = $order->getCard();

        $groupDoors = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->findBy([
                'card' => $card,
            ]);

        $buildingIds = [];
        foreach ($groupDoors as $door) {
            array_push($buildingIds, $door->getBuilding());
        }

        $card->setBuildingIds($buildingIds);

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($order->getUser());

        if (!is_null($user)) {
            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['user' => $user]);

            $info = [
                'username' => $profile->getName(),
                'user_phone' => $user->getPhone(),
                'user_email' => $user->getEmail(),
                'user_card_no' => $user->getCardNo(),
            ];

            $order->setUserInfo($info);
        }

        $view = new View($order);

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
                ['key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_CUSTOMER],
            ],
            $OpLevel
        );
    }
}
