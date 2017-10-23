<?php

namespace Sandbox\AdminApiBundle\Controller\MembershipCard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Traits\FinanceSalesExportTraits;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Admin MembershipCard Order Controller.
 */
class AdminMembershipCardOrderController extends SandboxRestController
{
    use FinanceSalesExportTraits;

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
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="company id"
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
        $companyId = $paramFetcher->get('company');
        $userId = $paramFetcher->get('user');

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
                null,
                $companyId,
                null,
                $userId
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
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="company id"
     * )
     *
     * @Method({"GET"})
     * @Route("/membership/cards/orders/export")
     *
     * @return View
     */
    public function getMembershipOrderExportAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
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

        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MEMBERSHIP_CARD_ORDER],
            ],
            AdminPermission::OP_LEVEL_VIEW,
            $adminPlatform->getPlatform()
        );

        $language = $paramFetcher->get('language');
        $channel = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $buildingId = $paramFetcher->get('building');
        $createDateRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $companyId = $paramFetcher->get('company');

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
                null,
                $companyId
            );

        return $this->getMembershipOrderExport(
            $language,
            $orders
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
        $this->checkMembershipCardOrderPermission(AdminPermission::OP_LEVEL_VIEW);

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getAdminOrderById(
                $id
            );

        if (is_null($order)) {
            return new View();
        }

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
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MEMBERSHIP_CARD_ORDER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER],
            ],
            $OpLevel
        );
    }
}
