<?php

namespace Sandbox\AdminShopApiBundle\Controller\User;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            array(
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
                ),
                array(
                    'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        // return result according to ids
        if (is_null($ids) || empty($ids)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $shopIds = $this->getMyShopIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SHOP_SHOP_ORDER,
                AdminPermission::KEY_SHOP_SHOP_KITCHEN,
            ),
            AdminPermission::OP_LEVEL_VIEW
        );

        $userIds = [];
        foreach ($ids as $id) {
            $users = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesUser')
                ->getShopUser(
                    $id,
                    $shopIds
                );

            if (!empty($users)) {
                array_push($userIds, $id);
            }
        }

        // get user
        $response = array();
        $users = $this->getRepo('User\UserView')->getUsersByIds($ids);
        foreach ($users as $user) {
            if (!is_null($user['phone'])) {
                $hidePhone = substr_replace($user['phone'], '****', 3, 4);
                $user['phone'] = $hidePhone;
            }

            array_push($response, $user);
        }

        return new View($response);
    }
}
