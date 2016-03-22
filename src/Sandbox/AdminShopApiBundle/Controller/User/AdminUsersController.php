<?php

namespace Sandbox\AdminShopApiBundle\Controller\User;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sandbox\ApiBundle\Entity\User;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Form\User\UserType;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Traits\StringUtil;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin controller.
 *
 * @category Sandbox
 *
 * @author   Mike he <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminUsersController extends ShopRestController
{
    use DoorAccessTrait;
    use StringUtil;

    /**
     * List all users.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by id"
     * )
     *
     * @Method({"GET"})
     * @Route("/users")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('id');

        // check user permission
        $this->throwAccessDeniedIfShopAdminNotAllowed(
            $this->getAdminId(),
            ShopAdminType::KEY_PLATFORM,
            array(
                ShopAdminPermission::KEY_SHOP_ORDER,
                ShopAdminPermission::KEY_SHOP_KITCHEN,
            ),
            ShopAdminPermissionMap::OP_LEVEL_VIEW
        );

        // return result according to ids
        if (is_null($ids) || empty($ids)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // ids is not null
        return $this->getUsersByIds($ids);
    }

    /**
     * @param $ids
     *
     * @return View
     */
    public function getUsersByIds(
        $ids
    ) {
        // get user
        $user = $this->getRepo('User\UserView')->getUsersByIds($ids);

        // set view
        return new View($user);
    }
}
