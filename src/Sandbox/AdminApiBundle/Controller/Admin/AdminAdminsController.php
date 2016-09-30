<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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
 * @link     http://www.Sandbox.cn/
 */
class AdminAdminsController extends SandboxRestController
{
    const ADMINS_ALL_ADMIN = '所有管理员';
    const ADMINS_SUPER_ADMIN = '超级管理员';
    const ADMINS_PLATFORM_ADMIN = '平台管理员';

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
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_VIEW);

        $cookies = $this->getPlatformCookies();
        $platform = $cookies['platform'];
        $companyId = $cookies['sales_company_id'];
        $isSuperAdmin = $paramFetcher->get('isSuperAdmin');
        $buildingId = $paramFetcher->get('building');
        $shopId = $paramFetcher->get('shop');
        $position = $paramFetcher->get('position');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $search = $paramFetcher->get('search');
        $type = $paramFetcher->get('type');

        $positionIds = is_null($position) ? null : explode(',', $position);

        $users = null;
        if (!is_null($search)) {
            $users = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->searchUserIds($search);
        }

        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getPositions(
                $platform,
                $companyId,
                $isSuperAdmin,
                $positionIds,
                $type
            );

        $userIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindUser(
                $positions,
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
            if ($platform == AdminPosition::PLATFORM_SALES) {
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
            if ($platform == AdminPosition::PLATFORM_SHOP) {
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

            $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->find($userId['userId']);

            $bind = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getBindingsByUser(
                    $userId['userId'],
                    $platform,
                    $companyId
                );

            $result[] = array(
                'user_id' => $userId['userId'],
                'user' => $user,
                'position' => $positionArr,
                'position_count' => count($positionArr),
                'building' => $buildingArr,
                'shop' => $shopArr,
                'bind' => $bind,
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
     * get admins menu.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_VIEW);

        $cookies = $this->getPlatformCookies();
        $platform = $cookies['platform'];
        $companyId = $cookies['sales_company_id'];

        $positions = $this->getDoctrine()
           ->getRepository('SandboxApiBundle:Admin\AdminPosition')
           ->getAdminPositions(
               $platform,
               null,
               $companyId
           );

        $allUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindUser($positions);

        $allAdmin = array(
            'key' => self::ADMINS_MENU_KEY_ALL,
            'name' => self::ADMINS_ALL_ADMIN,
            'count' => count($allUser),
       );

        $superPositions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getPositions(
                $platform,
                $companyId,
                true
            );

        $superUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindUser($superPositions);

        $superAdmin = array(
            'key' => self::ADMINS_MENU_KEY_SUPER,
            'name' => self::ADMINS_SUPER_ADMIN,
            'count' => count($superUser),
        );

        $buildingAdmin = array();
        $shopAdmin = array();

        switch ($platform) {
            case AdminPosition::PLATFORM_OFFICIAL:
                $platformPositions = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                    ->getPositions(
                        $platform,
                        null,
                        false
                    );
                $allPlatformUser = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindUser($platformPositions);
                break;
            case AdminPosition::PLATFORM_SALES:
                $platformPositions = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                    ->getAdminPositions(
                        $platform,
                        AdminPermission::PERMISSION_LEVEL_GLOBAL,
                        $companyId
                    );

                $allPlatformUser = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindUser($platformPositions);

                $myBuildings = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->getCompanyBuildings($companyId);

                foreach ($myBuildings as $myBuilding) {
                    $buildingUsers = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                        ->getBindUser(null, $myBuilding);

                    $buildingAdmin[] = array(
                        'key' => self::ADMINS_MENU_KEY_BUILDING,
                        'id' => $myBuilding->getId(),
                        'name' => $myBuilding->getname(),
                        'count' => count($buildingUsers),
                    );
                }
                break;
            case AdminPosition::PLATFORM_SHOP:
                $platformPositions = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPosition')
                    ->getAdminPositions(
                        $platform,
                        AdminPermission::PERMISSION_LEVEL_GLOBAL,
                        $companyId
                    );

                $allPlatformUser = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindUser($platformPositions);

                $myshops = $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\Shop')
                    ->getShopsByCompany($companyId);

                foreach ($myshops as $myshop) {
                    $shopUsers = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                        ->getBindUser(null, null, $myshop);

                    $shopAdmin[] = array(
                        'key' => self::ADMINS_MENU_KEY_SHOP,
                        'id' => $myshop->getId(),
                        'name' => $myshop->getname(),
                        'count' => count($shopUsers),
                    );
                }
                break;
            default:
                return new View();
        }

        $platformAdmin = array(
            'key' => self::ADMINS_MENU_KEY_PLATFORM,
            'name' => self::ADMINS_PLATFORM_ADMIN,
            'count' => count($allPlatformUser),
       );

        $result = array($allAdmin, $superAdmin, $platformAdmin);

        $result = array_merge($result, $buildingAdmin, $shopAdmin);

        return new View($result);
    }

    /**
     * get admins Position Menu.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
        $this->checkAdminAdvertisingPermission(AdminPermission::OP_LEVEL_VIEW);

        $cookies = $this->getPlatformCookies();
        $platform = $cookies['platform'];
        $companyId = $cookies['sales_company_id'];
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
        if ($key == self::ADMINS_MENU_KEY_PLATFORM) {
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

                $positionArr[] = array(
                    'key' => 'position',
                    'id' => $position->getId(),
                    'name' => $position->getName(),
                    'icon' => $global_image_url.$position->getIcon()->getIcon(),
                    'count' => count($positionUser),
                );
            }
        } elseif ($key == self::ADMINS_MENU_KEY_SUPER) {
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

                $positionArr[] = array(
                    'key' => 'position',
                    'id' => $position->getId(),
                    'name' => $position->getName(),
                    'icon' => $global_image_url.$position->getIcon()->getIcon(),
                    'count' => count($positionUser),
                );
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
                $positionArr[] = array(
                    'key' => 'position',
                    'id' => $position->getId(),
                    'name' => $position->getName(),
                    'icon' => $global_image_url.$position->getIcon()->getIcon(),
                    'count' => count($positionUser),
                );
            }
        }

        return $positionArr;
    }

    /**
     * Check user permission.
     *
     * @param int $OpLevel
     */
    private function checkAdminAdvertisingPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN],
                ['key' => AdminPermission::KEY_SHOP_PLATFORM_ADMIN],
            ],
            $OpLevel
        );
    }
}
