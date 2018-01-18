<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;

/**
 * Admin controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminAdminsController extends SandboxRestController
{
    const ADMINS_ALL_ADMIN = 'admin.admins.all';
    const ADMINS_SUPER_ADMIN = 'admin.admins.super';
    const ADMINS_PLATFORM_ADMIN = 'admin.admins.platform';

    const ADMINS_MENU_KEY_ALL = 'all';
    const ADMINS_MENU_KEY_SUPER = 'super';
    const ADMINS_MENU_KEY_PLATFORM = 'platform';
    const ADMINS_MENU_KEY_BUILDING = 'building';
    const ADMINS_MENU_KEY_SHOP = 'shop';

    /**
     * List all admins.
     *
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="name or username"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="global|specify"
     * )
     *
     * @Annotations\QueryParam(
     *    name="isSuperAdmin",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="isSuperAdmin"
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="shop"
     * )
     *
     * @Annotations\QueryParam(
     *    name="position",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="position"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
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
     * @Route("/admins")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        $isSuperAdmin = $paramFetcher->get('isSuperAdmin');
        $buildingId = $paramFetcher->get('building');
        $shopId = $paramFetcher->get('shop');
        $position = $paramFetcher->get('position');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $search = $paramFetcher->get('search');
        $type = $paramFetcher->get('type');

        $users = null;
        if (!is_null($search)) {
            $users = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->searchUserIds($search);
        }

        $positionIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getPositionIds(
                $platform,
                $companyId,
                $isSuperAdmin,
                $position,
                $type
            );

        if (AdminPermission::PERMISSION_LEVEL_GLOBAL == $type ||
            self::ADMINS_MENU_KEY_SUPER == $type
        ) {
            $superPositionId = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                ->getPositionIds(
                    $platform,
                    $companyId,
                    true
                );

            $positionIds = array_merge($positionIds, $superPositionId);
        }

        $userIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindUser(
                $positionIds,
                $buildingId,
                $shopId,
                $users
            );

        $result = array();
        foreach ($userIds as $userId) {
            $positionBinds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getBindUserInfo(
                    $userId['userId'],
                    $platform,
                    $companyId
                );
            $positionArr = array();
            foreach ($positionBinds as $positionBind) {
                $position = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                    ->find($positionBind['id']);
                $positionArr[] = $position;
            }

            $buildingArr = array();
            if (AdminPosition::PLATFORM_SALES == $platform) {
                $buildingBinds = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindBuilding(
                        $userId['userId'],
                        $platform,
                        $companyId
                    );

                foreach ($buildingBinds as $buildingBind) {
                    $buildingInfo = $this->getDoctrine()->getRepository("SandboxApiBundle:Room\RoomBuilding")
                        ->find($buildingBind['buildingId']);
                    $buildingArr[] = $buildingInfo;
                }
            }

            $shopArr = array();
            if (AdminPosition::PLATFORM_SHOP == $platform) {
                $shopBinds = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindShop(
                        $userId['userId'],
                        $platform,
                        $companyId
                    );

                foreach ($shopBinds as $shopBind) {
                    $shopInfo = $this->getDoctrine()->getRepository("SandboxApiBundle:Shop\Shop")
                        ->find($shopBind['shopId']);
                    $shopArr[] = $shopInfo;
                }
            }

//            $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->find($userId['userId']);

            $bind = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getBindingsByUser(
                    $userId['userId'],
                    $platform,
                    $companyId
                );

            $bind = $this->get('serializer')->serialize(
                $bind,
                'json',
                SerializationContext::create()->setGroups(['admin_position_bind_view'])
            );
            $bind = json_decode($bind, true);

            $adminProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy([
                    'userId' => $userId['userId'],
                    'salesCompanyId' => $companyId,
                ]);

            if (is_null($adminProfile)) {
                $adminProfile = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy([
                        'userId' => $userId['userId'],
                        'salesCompanyId' => null,
                    ]);
            }

            $admin = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                ->findOneBy(['userId' => $userId['userId']]);
            $adminPhone = is_null($admin) ? null : $admin->getPhone();

            $result[] = array(
                'user_id' => $userId['userId'],
//                'user' => $user,
                'position' => $positionArr,
                'position_count' => count($positionArr),
                'building' => $buildingArr,
                'shop' => $shopArr,
                'bind' => $bind,
                'admin_profile' => $adminProfile,
                'admin' => [
                    'phone' => $adminPhone,
                ],
            );
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $result,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Extra Admins.
     *
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="name or username"
     * )
     *
     * @Annotations\QueryParam(
     *    name="position",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="position"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="Building Id"
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="Shop Id"
     * )
     *
     * @Method({"GET"})
     * @Route("/extra/admins")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getExtraAdminsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];
        $position = $paramFetcher->get('position');
        $building = $paramFetcher->get('building');
        $shop = $paramFetcher->get('shop');
        $search = $paramFetcher->get('search');

        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getPositionIds(
                $platform,
                $companyId
            );

        $userIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindUser(
                $positions
            );
        $allUser = array();
        foreach ($userIds as $userId) {
            $allUser[] = $userId['userId'];
        }

        $bindUser = array();
        if (!is_null($position)) {
            $PositionBindUsers = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getBindUser(
                    $position,
                    $building,
                    $shop
                );

            foreach ($PositionBindUsers as $PositionBindUser) {
                $bindUser[] = $PositionBindUser['userId'];
            }
        }

        $diff = array_diff($allUser, $bindUser);

        $result = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->searchUserInfo($diff, $search);

        $response = [];
        foreach ($result as $item) {
            $userId = $item->getId();

            $adminProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy([
                    'userId' => $userId,
                    'salesCompanyId' => $companyId,
                ]);

            if (is_null($adminProfile)) {
                $adminProfile = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy([
                        'userId' => $userId,
                        'salesCompanyId' => null,
                    ]);
            }

            $admin = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                ->findOneBy(['userId' => $userId]);
            $adminPhone = is_null($admin) ? null : $admin->getPhone();

            array_push($response, [
                'user_id' => $userId,
                'admin_profile' => $adminProfile,
                'admin' => [
                    'phone' => $adminPhone,
                ],
            ]);
        }

        return new View($response);
    }

    /**
     * get admins menu.
     *
     * @param Request $request the request object
     *
     * @Method({"GET"})
     * @Route("/admins/menu")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminsMenu(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        $allPositionIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getPositionIds(
                $platform,
                $companyId
           );

        $allUserCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->countBindUser(
                $allPositionIds
            );

        $superPositionId = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getPositionIds(
                $platform,
                $companyId,
                true
            );

        $superAdminsCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->countBindUser(
                $superPositionId
            );

        $result = array(
            array(
                'key' => self::ADMINS_MENU_KEY_ALL,
                'name' => $this->get('translator')->trans(self::ADMINS_ALL_ADMIN),
                'count' => $allUserCount,
            ),
            array(
                'key' => self::ADMINS_MENU_KEY_SUPER,
                'name' => $this->get('translator')->trans(self::ADMINS_SUPER_ADMIN),
                'count' => $superAdminsCount,
            ),
        );

        if (AdminPosition::PLATFORM_SALES == $platform) {
            $myBuildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->getCompanyBuildings($companyId);

            $buildingAdmin = array();
            foreach ($myBuildings as $myBuilding) {
                $buildingUsersCount = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->countBindUser(
                        null,
                        $myBuilding
                    );

                $buildingAdmin[] = array(
                    'key' => self::ADMINS_MENU_KEY_BUILDING,
                    'id' => $myBuilding->getId(),
                    'name' => $myBuilding->getname(),
                    'count' => $buildingUsersCount,
                );
            }

            $result = array_merge($result, $buildingAdmin);
        }

        if (AdminPosition::PLATFORM_SHOP == $platform) {
            $myShops = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Shop\Shop')
                ->getShopsByCompany($companyId);

            $shopAdmin = array();
            foreach ($myShops as $myShop) {
                $shopUsersCount = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->countBindUser(
                        null,
                        null,
                        $myShop
                    );

                $shopAdmin[] = array(
                    'key' => self::ADMINS_MENU_KEY_SHOP,
                    'id' => $myShop->getId(),
                    'name' => $myShop->getname(),
                    'count' => $shopUsersCount,
                );
            }

            $result = array_merge($result, $shopAdmin);
        }

        return new View($result);
    }

    /**
     * get admins Position Menu.
     *
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="key",
     *    array=false,
     *    nullable=false,
     *    strict=true,
     *    description="key"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="shop"
     * )
     *
     * @Method({"GET"})
     * @Route("/admins/position/menu")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminsPositionMenu(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];
        $companyId = $adminPlatform['sales_company_id'];

        $key = $paramFetcher->get('key');
        $buildingId = $paramFetcher->get('building');
        $shopId = $paramFetcher->get('shop');

        $positions = $this->getPositions(
            $key,
            $platform,
            $companyId,
            $buildingId,
            $shopId
        );

        return new View($positions);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="phone",
     *     array=false,
     *     nullable=false,
     *     strict=true
     * )
     *
     * @Route("/admins/search")
     * @Method({"GET"})
     *
     * @return View
     */
    public function searchAdminsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $phone = $paramFetcher->get('phone');

        $admins = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->searchAdmins(
                $phone
            );

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $response = [];
        foreach ($admins as $admin) {
            $userId = $admin['user_id'];

            $adminProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy([
                    'userId' => $userId,
                    'salesCompanyId' => $salesCompanyId,
                ]);

            if (is_null($adminProfile)) {
                $adminProfile = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy([
                        'userId' => $userId,
                        'salesCompanyId' => null,
                    ]);
            }

            if ($adminProfile) {
                $admin['avatar'] = $adminProfile->getAvatar();
                $admin['nickname'] = $adminProfile->getNickname();
            }

            array_push($response, $admin);
        }

        return new View($response);
    }

    /**
     * @param $key
     * @param $platform
     * @param $companyId
     * @param $buildingId
     * @param $shopId
     *
     * @return array|View
     */
    private function getPositions(
        $key,
        $platform,
        $companyId,
        $buildingId,
        $shopId
    ) {
        $global_image_url = $this->container->getParameter('image_url');

        $positionArr = array();
        if (self::ADMINS_MENU_KEY_PLATFORM == $key) {
            $positions = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                ->getPositions(
                    $platform,
                    $companyId,
                    true
                );

            foreach ($positions as $position) {
                $positionUser = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindUser($position);

                if (count($positionUser) > 0) {
                    $positionArr[] = array(
                        'key' => 'position',
                        'id' => $position->getId(),
                        'name' => $position->getName(),
                        'icon' => $global_image_url.$position->getIcon()->getIcon(),
                        'count' => count($positionUser),
                        'position' => $position,
                    );
                }
            }

            switch ($platform) {
                case AdminPosition::PLATFORM_OFFICIAL:
                    $positions = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                        ->getPositions(
                            $platform,
                            null,
                            false
                        );
                    break;
                case AdminPosition::PLATFORM_SALES:
                    $positions = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                        ->getAdminPositions(
                            $platform,
                            AdminPermission::PERMISSION_LEVEL_GLOBAL,
                            $companyId
                        );
                    break;
                case AdminPosition::PLATFORM_SHOP:
                    $positions = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                        ->getAdminPositions(
                            $platform,
                            AdminPermission::PERMISSION_LEVEL_GLOBAL,
                            $companyId
                        );
                    break;
                default:
                    return new View();
            }

            foreach ($positions as $position) {
                $positionUser = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindUser($position, $buildingId, $shopId);

                if (count($positionUser) > 0) {
                    $positionArr[] = array(
                        'key' => 'position',
                        'id' => $position->getId(),
                        'name' => $position->getName(),
                        'icon' => $global_image_url.$position->getIcon()->getIcon(),
                        'count' => count($positionUser),
                        'position' => $position,
                    );
                }
            }
        } else {
            $positions = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getBuildingPosition(
                    $platform,
                    $buildingId,
                    $shopId
                );

            foreach ($positions as $position) {
                $positionUser = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindUser($position['positionId'], $buildingId, $shopId);

                $position = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                    ->find($position['positionId']);

                if (count($positionUser) > 0) {
                    $positionArr[] = array(
                        'key' => 'position',
                        'id' => $position->getId(),
                        'name' => $position->getName(),
                        'icon' => $global_image_url.$position->getIcon()->getIcon(),
                        'count' => count($positionUser),
                        'position' => $position,
                    );
                }
            }
        }

        return $positionArr;
    }

    /**
     * Check user permission.
     *
     * @param int $OpLevel
     */
    private function checkAdminPermission(
        $OpLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SHOP_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING],
                ['key' => AdminPermission::KEY_SALES_BUILDING_SPACE],
                ['key' => AdminPermission::KEY_SALES_BUILDING_BUILDING],
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_ADMIN],
            ],
            $OpLevel
        );
    }
}
