<?php

namespace Sandbox\AdminApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Knp\Component\Pager\Paginator;
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

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $flows = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserBeanFlow')
            ->findBy(
                array('userId' => $userId),
                array('creationDate' => 'DESC')
            );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $flows,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
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
