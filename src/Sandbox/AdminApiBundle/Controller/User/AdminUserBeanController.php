<?php

namespace Sandbox\AdminApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\BeanConstants;
use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;

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

//        foreach ($flows as $flow) {
//            $source = $this->get('translator')->trans(BeanConstants::TRANS_USER_BEAN.$flow->getSource());
//
//            $flow->setSource($source);
//        }

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
