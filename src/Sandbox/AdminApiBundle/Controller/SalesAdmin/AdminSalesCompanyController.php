<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminExcludePermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionGroupBinding;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionUserBinding;
use Sandbox\ApiBundle\Entity\Finance\FinanceSalesWallet;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyApply;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyView;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyPostType;
use Sandbox\ApiBundle\Form\SalesAdmin\ServiceInfoPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Doctrine\ORM\EntityManager;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;

/**
 * SalesAdmin controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminSalesCompanyController extends SandboxRestController
{
    const POSITION_ADMIN = '超级管理员';
    const POSITION_COFFEE_ADMIN = '超级管理员';

    const ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_CODE = 400005;
    const ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_MESSAGE = 'Over the super administrator limit number';
    const ERROR_NOT_NULL_SUPER_ADMIN_CODE = 400006;
    const ERROR_NOT_NULL_SUPER_ADMIN_MESSAGE = 'Must at least one super administrator position binding';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/companies/exclude_permissions_options")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getExcludePermissionsOptionsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $excludePermission = array(
            'exclude_permissions_options' => array(
                array(
                    'group_key' => AdminPermissionGroups::GROUP_KEY_EVENT,
                    'group_name' => '活动管理',
                    'permissions' => array(
                        array('key' => AdminPermission::KEY_SALES_PLATFORM_EVENT),
                        array('key' => AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER),
                    ),
                ),
                array(
                    'group_key' => AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD,
                    'group_name' => '会员卡管理',
                    'permissions' => array(
                        array('key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD),
                        array('key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER),
                        array('key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_PRODUCT),
                    ),
                ),
                array(
                    'group_key' => AdminPermissionGroups::GROUP_KEY_SERVICE,
                    'group_name' => '服务管理',
                    'permissions' => array(
                        array('key' => AdminPermission::KEY_SALES_PLATFORM_SERVICE),
                        array('key' => AdminPermission::KEY_SALES_PLATFORM_SERVICE_ORDER),
                    ),
                ),
            ),
        );

        return new View($excludePermission);
    }

    /**
     * @param Request $request
     *
     * @Route("/companies/dropdown")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesCompaniesDropDownAction(
        Request $request
    ) {
        $companies = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->getSalesCompanies();

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['dropdown']));
        $view->setData($companies);

        return $view;
    }

    /**
     * List all companies.
     *
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *     name="status",
     *     array=false,
     *     nullable=true,
     *     strict=true
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
     * @Annotations\QueryParam(
     *    name="keyword",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="search keyword"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Method({"GET"})
     * @Route("/companies")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getSalesCompaniesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES_MONITORING],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        $status = $paramFetcher->get('status');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $salesCompanies = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyView')
            ->getCompanyList(
                $status,
                $keyword,
                $keywordSearch,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyView')
            ->countCompanyList(
                $status,
                $keyword,
                $keywordSearch
            );

        foreach ($salesCompanies as &$company) {
            if ($company['type'] == SalesCompanyView::TYPE_COMPANY) {
                $buildingCounts = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->countSalesBuildings($company['id']);

                $company['building_counts'] = (int) $buildingCounts;

                $shopCounts = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Shop\Shop')
                    ->countShopsByCompany($company['id']);

                $company['shop_counts'] = (int) $shopCounts;

                $adminsCount = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->countBindUserByPlatform(
                        AdminPermission::PERMISSION_PLATFORM_SALES,
                        $company['id'],
                        true
                    );

                $company['admins'] = (int) $adminsCount;

                $coffeeAdminsCount = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->countBindUserByPlatform(
                        AdminPermission::PERMISSION_PLATFORM_SHOP,
                        $company['id'],
                        true
                    );

                $company['coffee_admins'] = (int) $coffeeAdminsCount;

                $productCount = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->countsProductsByCompany(
                        $company['id']
                    );

                $company['product_counts'] = (int) $productCount;

                $tradeTypes = [
                    SalesCompanyServiceInfos::TRADE_TYPE_ACTIVITY,
                    SalesCompanyServiceInfos::TRADE_TYPE_LONGTERM,
                    SalesCompanyServiceInfos::TRADE_TYPE_MEMBERSHIP_CARD,
                    SalesCompanyServiceInfos::TRADE_TYPE_SERVICE,
                ];

                $serviceCount = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                    ->countServices(
                        $company['id'],
                        $tradeTypes
                    );

                $company['service_counts'] = (int) $serviceCount;
            }
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $salesCompanies,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * List definite id of company.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Method({"GET"})
     * @Route("/companies/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCompanyAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_VIEW);

        $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($id);
        if (is_null($company)) {
            return new View();
        }

        // set admins
        $adminPosition = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(
                array(
                    'salesCompany' => $company,
                    'name' => self::POSITION_ADMIN,
                    'platform' => AdminPermission::PERMISSION_PLATFORM_SALES,
                    'isSuperAdmin' => true,
                )
            );

        $admins = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findBy(array('position' => $adminPosition));

        $userArray = [];
        foreach ($admins as $admin) {
            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->find($admin->getUserId());

            $adminProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy([
                    'userId' => $admin->getUserId(),
                    'salesCompanyId' => null,
                ]);

            $avatar = !is_null($adminProfile) ? $adminProfile->getAvatar() : '';
            $name = !is_null($adminProfile) ? $adminProfile->getNickname() : '';

            $user->setAdminProfile([
                'avatar' => $avatar,
                'name' => $name,
            ]);

            array_push($userArray, $user);
        }

        $company->setAdmins($userArray);

        // set coffee admins
        $coffeeAdminPosition = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(
                array(
                    'salesCompany' => $company,
                    'name' => self::POSITION_COFFEE_ADMIN,
                    'platform' => AdminPermission::PERMISSION_PLATFORM_SHOP,
                    'isSuperAdmin' => true,
                )
            );

        $coffeeAdmins = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findBy(array('position' => $coffeeAdminPosition));

        $coffeeUserArray = [];
        foreach ($coffeeAdmins as $coffeeAdmin) {
            $coffeeUser = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->find($coffeeAdmin->getUserId());

            $adminProfile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy([
                    'userId' => $coffeeAdmin->getUserId(),
                    'salesCompanyId' => null,
                ]);

            $avatar = !is_null($adminProfile) ? $adminProfile->getAvatar() : '';
            $name = !is_null($adminProfile) ? $adminProfile->getNickname() : '';

            $coffeeUser->setAdminProfile([
                'avatar' => $avatar,
                'name' => $name,
            ]);

            array_push($coffeeUserArray, $coffeeUser);
        }

        $company->setCoffeeAdmins($coffeeUserArray);

        // building and shop counts
        $buildingCounts = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->countSalesBuildings($company);
        $shops = $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\Shop')->getShopsByCompany($company);
        $shopCounts = count($shops);

        $company->setBuildingCounts((int) $buildingCounts);
        $company->setShopCounts((int) $shopCounts);

        // new pending shop
        foreach ($shops as $shop) {
            if (!$shop->isActive() && !$shop->isDeleted()) {
                $company->setHasPendingShop(true);
            }
        }

        // check exclude permission groups
        $this->checkExcludePermissions($company);

        $services = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findBy(['company' => $company]);

        $company->setServices($services);

        // set view
        $view = new View($company);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_view']));

        return $view;
    }

    /**
     * @param SalesCompany $company
     */
    private function checkExcludePermissions(
        $company
    ) {
        $excludePermissions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminExcludePermission')
            ->findBy([
                'salesCompany' => $company,
            ]);

        $excludePermissionsArray = array();
        foreach ($excludePermissions as $excludePermission) {
            if (!is_null($excludePermission->getPermission()) || is_null($excludePermission->getGroup())) {
                continue;
            }

            array_push($excludePermissionsArray, $excludePermission->getGroup()->getGroupKey());
        }
        $company->setExcludePermissions($excludePermissionsArray);
    }

    /**
     * Create sales company.
     *
     * @param Request $request the request object
     *
     * @Method({"POST"})
     * @Route("/companies")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postSalesCompanyAction(
        Request $request
    ) {
        // check user permission
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        // save sales company
        $salesCompany = $this->saveSalesCompany($em, $request);

        $admins = $salesCompany->getAdmins();
        $coffeeAdmins = $salesCompany->getCoffeeAdmins();
        $servicesInfos = $salesCompany->getServices();
        $excludePermissions = $salesCompany->getExcludePermissions();

        // save admins
        $this->saveAdmins(
            $admins,
            $salesCompany,
            self::POSITION_ADMIN,
            AdminPermission::PERMISSION_PLATFORM_SALES
        );

        // save coffee admins
        $this->saveAdmins(
            $coffeeAdmins,
            $salesCompany,
            self::POSITION_COFFEE_ADMIN,
            AdminPermission::PERMISSION_PLATFORM_SHOP
        );

        // save services
        $this->saveServices(
            $em,
            $servicesInfos,
            $salesCompany
        );

        // save modules
        $this->saveExcludePermissions(
            $em,
            $excludePermissions,
            $salesCompany
        );

        $wallet = new FinanceSalesWallet();
        $wallet->setCompanyId($salesCompany->getId());

        $em->persist($wallet);

        // delete sales company application
        if (!is_null($salesCompany->getApplicationId())) {
            $application = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyApply')
                ->find($salesCompany->getApplicationId());

            if (!is_null($application)) {
                $application->setStatus(SalesCompanyApply::STATUS_CLOSED);
            }
        }

        $em->flush();

        $this->addDefaultPositionsForSales($salesCompany, $excludePermissions);

        // set view
        $view = new View();
        $view->setStatusCode(201);
        $view->setData(array(
            'id' => $salesCompany->getId(),
        ));

        return $view;
    }

    /**
     * Update Sales Company.
     *
     * @param Request $request the request object
     * @param int     $id      the admin ID
     *
     * @Route("/companies/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putSalesCompanyAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_EDIT);

        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($id);
        $this->throwNotFoundIfNull($salesCompany, CustomErrorMessagesConstants::ERROR_SALES_COMPANY_NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();

        $em->beginTransaction();

        try {
            // update sales company
            $form = $this->createForm(
                new SalesCompanyPostType(),
                $salesCompany,
                array(
                    'method' => 'PUT',
                )
            );
            $form->handleRequest($request);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_SALES_COMPANY_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
            }

            $admins = $salesCompany->getAdmins();
            $coffeeAdmins = $salesCompany->getCoffeeAdmins();
            $servicesInfos = $salesCompany->getServices();
            $excludePermissions = $salesCompany->getExcludePermissions();

            $isOnlineSales = $salesCompany->isOnlineSales();
            if (!$isOnlineSales) {
                $buildings = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->findBy(['companyId' => $id]);

                foreach ($buildings as $building) {
                    $products = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Product\Product')
                        ->getSalesProductsByBuilding($building);

                    foreach ($products as $product) {
                        $product->setVisible(false);
                    }
                }
            }

            // update admins
            $this->updateAdmins(
                $admins,
                $salesCompany,
                AdminPermission::PERMISSION_PLATFORM_SALES
            );

            // update coffee admins
            $this->updateAdmins(
                $coffeeAdmins,
                $salesCompany,
                AdminPermission::PERMISSION_PLATFORM_SHOP
            );

            // update services
            $this->saveServices(
                $em,
                $servicesInfos,
                $salesCompany
            );

            // update modules
            $this->saveExcludePermissions(
                $em,
                $excludePermissions,
                $salesCompany
            );

            $em->flush();

            $em->commit();
        } catch (\Exception $exception) {
            $em->rollback();

            throw new BadRequestHttpException(
                self::BAD_PARAM_MESSAGE
            );
        }

        return new View();
    }

    /**
     * Update company.
     *
     * @param Request $request the request object
     * @param int     $id      the admin ID
     *
     * @Route("/companies/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchCompanyAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_EDIT);

        $salesCompany = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($id);
        $this->throwNotFoundIfNull($salesCompany, self::NOT_FOUND_MESSAGE);

        $bannedOld = $salesCompany->isBanned();

        // bind data
        $companyJson = $this->container->get('serializer')->serialize($salesCompany, 'json');
        $patch = new Patch($companyJson, $request->getContent());
        $companyJson = $patch->apply();
        $form = $this->createForm(new SalesCompanyPatchType(), $salesCompany);
        $form->submit(json_decode($companyJson, true));

        $this->handleAdminBanned(
            $bannedOld,
            $salesCompany
        );

        return new View();
    }

    /**
     * @param $bannedOld
     * @param $salesCompany
     */
    private function handleAdminBanned(
        $bannedOld,
        $salesCompany
    ) {
        $banned = $salesCompany->isBanned();

        if ($bannedOld == $banned) {
            return;
        }

        // check user permission
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_EDIT);

        try {
            if ($banned) {
                $this->handleSalesAndShopsByCompany(
                    $salesCompany,
                    true,
                    false,
                    RoomBuilding::STATUS_BANNED,
                    false
                );
            } else {
                $this->handleSalesAndShopsByCompany(
                    $salesCompany,
                    false,
                    true,
                    RoomBuilding::STATUS_ACCEPT,
                    true
                );
            }
        } catch (\Exception $e) {
            error_log('Banned Error');
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param $user
     * @param $salesCompany
     * @param $name
     *
     * @return AdminPosition
     */
    private function createPosition(
        $user,
        $salesCompany,
        $name,
        $platform
    ) {
        $em = $this->getDoctrine()->getManager();

        $position = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy([
                'salesCompany' => $salesCompany,
                'name' => $name,
                'isSuperAdmin' => true,
                'platform' => $platform,
                'isHidden' => false,
            ]);

        if (is_null($position)) {
            $icon = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')
                ->find(1);

            $position = new AdminPosition();
            $position->setName($name);
            $position->setPlatform($platform);
            $position->setIsSuperAdmin(true);
            $position->setIcon($icon);
            $position->setSalesCompany($salesCompany);
            $em->persist($position);
        }

        $binding = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findOneBy([
                'user' => $user,
                'position' => $position,
            ]);

        if (is_null($binding)) {
            $adminPositionUser = new AdminPositionUserBinding();
            $adminPositionUser->setUser($user);
            $adminPositionUser->setPosition($position);
            $em->persist($adminPositionUser);
        }

        $em->flush();
    }

    /**
     * @param $company
     * @param $userIds
     */
    private function updatePosition(
        $company,
        $userIds,
        $platform
    ) {
        $em = $this->getDoctrine()->getManager();

        $position = $em->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(
                array(
                    'salesCompany' => $company,
                    'name' => self::POSITION_ADMIN,
                    'platform' => $platform,
                    'isSuperAdmin' => true,
                    'isHidden' => false,
                )
            );

        if (!is_null($position)) {
            $adminPositionUsers = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->findBy(array('position' => $position));

            foreach ($adminPositionUsers as $adminPositionUser) {
                $em->remove($adminPositionUser);
            }

            $em->flush();
        } else {
            $icon = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')
                ->find(1);

            $position = new AdminPosition();
            $position->setName(self::POSITION_ADMIN);
            $position->setPlatform($platform);
            $position->setIsSuperAdmin(true);
            $position->setIcon($icon);
            $position->setSalesCompany($company);
            $em->persist($position);
        }

        foreach ($userIds as $userId) {
            $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
            if (is_null($user)) {
                continue;
            }

            $binding = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->findOneBy([
                    'user' => $user,
                    'position' => $position,
                ]);

            if (is_null($binding)) {
                $adminPositionUser = new AdminPositionUserBinding();
                $adminPositionUser->setUser($user);
                $adminPositionUser->setPosition($position);
                $em->persist($adminPositionUser);
            }

            $salesAdmin = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                ->findOneBy(array('userId' => $userId));

            if (is_null($salesAdmin)) {
                $salesAdmin = new SalesAdmin();
                $salesAdmin->setUserId($userId);
                $salesAdmin->setPassword($user->getPassword());
                $salesAdmin->setXmppUsername('admin_'.$user->getXmppUsername());
                $salesAdmin->setPhoneCode($user->getPhoneCode());
                $salesAdmin->setPhone($user->getPhone());

                $em->persist($salesAdmin);
            }
        }
    }

    /**
     * @param $companyId
     * @param $platformAdminBanned
     * @param $buildingVisible
     * @param $buildingStatus
     * @param $shopOnline
     */
    private function handleSalesAndShopsByCompany(
        $companyId,
        $platformAdminBanned,
        $buildingVisible,
        $buildingStatus,
        $shopOnline
    ) {
        // set buildings visible and status
        $buildings = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->findByCompanyId($companyId);

        if (empty($buildings)) {
            return;
        }

        foreach ($buildings as $building) {
            // set buildings status
            $building->setStatus($buildingStatus);

            // set products & buildings visible
            if (!$platformAdminBanned) {
                continue;
            }

            // building offline
            $building->setVisible($buildingVisible);

            // hide all products by buildings
            $this->hideAllProductsByBuilding(
                $building
            );

            // set shops
            $shops = $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\Shop')->findByBuilding($building);

            if (empty($shops)) {
                continue;
            }

            foreach ($shops as $shop) {
                if (is_null($shop)) {
                    continue;
                }

                $shop->setOnline($shopOnline);

                // set shop & shop products offline
                if (!$shopOnline) {
                    $shop->setClose(true);

                    // set shop products offline
                    $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\ShopProduct')->setShopProductsOfflineByShopId(
                        $shop->getId()
                    );
                }
            }
        }
    }

    /**
     * @param RoomBuilding $building
     */
    private function hideAllProductsByBuilding(
        $building
    ) {
        // hide all of the products
        $products = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->getSalesProductsByBuilding($building);

        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {
            $product->setVisible(false);
        }
    }

    /**
     * @param $company
     * @param $status
     * @param $roomType
     */
    private function hideAllProductsByRoomType(
        $company,
        $status,
        $roomType
    ) {
        if (false == $status) {
            $products = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->findProductsByType(
                    $company,
                    $roomType
                );

            foreach ($products as $product) {
                $product->setVisible(false);
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param array         $excludePermissions
     * @param SalesCompany  $salesCompany
     */
    private function saveExcludePermissions(
        $em,
        $excludePermissions,
        $salesCompany
    ) {
        if (is_null($excludePermissions)) {
            return;
        }

        // remove old data
        $excludePermissionsRemove = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminExcludePermission')
            ->findBy(array(
                'salesCompany' => $salesCompany,
            ));

        foreach ($excludePermissionsRemove as $excludePermission) {
            $em->remove($excludePermission);
        }

        $em->flush();

        foreach ($excludePermissions as $excludePermission) {
            if (!array_key_exists('permissions', $excludePermission)
            && !array_key_exists('group_key', $excludePermission)) {
                continue;
            }

            // set exclude permission groups
            $permissionGroupKey = $excludePermission['group_key'];
            $permissionGroup = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
                ->findOneBy(array(
                    'groupKey' => $permissionGroupKey,
                    'platform' => AdminPermissionGroups::GROUP_PLATFORM_SALES,
                ));

            if (!is_null($permissionGroup)) {
                $excludePermissionGroupEm = new AdminExcludePermission();
                $excludePermissionGroupEm->setSalesCompany($salesCompany);
                $excludePermissionGroupEm->setGroup($permissionGroup);
                $excludePermissionGroupEm->setPlatform(AdminPosition::PLATFORM_SALES);

                $em->persist($excludePermissionGroupEm);
            }

            // set exclude permissions
            foreach ($excludePermission['permissions'] as $permissionKey) {
                $permission = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                    ->findOneBy(array(
                        'key' => $permissionKey['key'],
                    ));

                $excludePermissionEm = new AdminExcludePermission();
                $excludePermissionEm->setSalesCompany($salesCompany);
                $excludePermissionEm->setPermission($permission);
                $excludePermissionEm->setPlatform(AdminPosition::PLATFORM_SALES);

                $em->persist($excludePermissionEm);
            }
        }
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    protected function checkSalesAdminPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES],
            ],
            $opLevel
        );
    }

    /**
     * @param $em
     * @param $request
     *
     * @return SalesCompany
     */
    private function saveSalesCompany(
        $em,
        $request
    ) {
        $salesCompany = new SalesCompany();
        $form = $this->createForm(
            new SalesCompanyPostType(),
            $salesCompany
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_SALES_COMPANY_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
        }

        $em->persist($salesCompany);

        return $salesCompany;
    }

    /**
     * @param $admins
     * @param $salesCompany
     * @param $positionName
     */
    private function saveAdmins(
        $admins,
        $salesCompany,
        $positionName,
        $platform
    ) {
        if (is_null($admins) || empty($admins)) {
            return;
        }

        if (count($admins) > 2) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_CAN_NOT_MORE_THAN_TWO_ADMINS);
        }

        foreach ($admins as $adminId) {
            if (is_null($adminId) || empty($adminId)) {
                continue;
            }
            $admin = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')->find($adminId);
            $this->throwNotFoundIfNull($admin, CustomErrorMessagesConstants::ERROR_ADMIN_NOT_FOUND_MESSAGE);

            $this->createPosition(
                $admin,
                $salesCompany,
                $positionName,
                $platform
            );
        }
    }

    /**
     * @param $em
     * @param $servicesInfos
     * @param $salesCompany
     */
    private function saveServices(
        $em,
        $servicesInfos,
        $salesCompany
    ) {
        if (is_null($servicesInfos) || empty($servicesInfos)) {
            return;
        }

        $method = 'POST';
        foreach ($servicesInfos as $serviceInfo) {
            $service = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->findOneBy(
                    array(
                        'tradeTypes' => $serviceInfo['trade_types'],
                        'company' => $salesCompany,
                    )
                );

            if (is_null($service)) {
                $service = new SalesCompanyServiceInfos();
            } else {
                $method = 'PUT';

                // set visible of product to false if closing the service
                $this->hideAllProductsByRoomType(
                    $salesCompany,
                    $serviceInfo['status'],
                    $service->getTradeTypes()
                );
            }

            $form = $this->createForm(
                new ServiceInfoPostType(),
                $service,
                array(
                    'method' => $method,
                )
            );
            $form->submit($serviceInfo, false);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_SERVICE_INFO_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE);
            }

            if (SalesCompanyServiceInfos::TRADE_TYPE_LONGTERM == $serviceInfo['trade_types']) {
                $service->setCollectionMethod('');
            }

            $service->setCompany($salesCompany);

            $em->persist($service);
        }
    }

    /**
     * @param $admins
     * @param $salesCompany
     */
    private function updateAdmins(
        $admins,
        $salesCompany,
        $platform
    ) {
        if (is_null($admins) || empty($admins)) {
            return;
        }

        if (count($admins) > 2) {
            throw new BadRequestHttpException(CustomErrorMessagesConstants::ERROR_CAN_NOT_MORE_THAN_TWO_ADMINS);
        }

        $this->updatePosition(
            $salesCompany,
            $admins,
            $platform
        );
    }

    /**
     * @param SalesCompany $salesCompany
     * @param              $excludePermissions
     */
    private function addDefaultPositionsForSales(
        $salesCompany,
        $excludePermissions
    ) {
        $em = $this->getDoctrine()->getManager();

        $this->addGeneralManagerPosition(
            $em,
            $salesCompany,
            $excludePermissions
        );

        $this->addManagerPosition(
            $em,
            $salesCompany,
            $excludePermissions
        );

        $this->addStaffPosition(
            $em,
            $salesCompany,
            $excludePermissions
        );

        $this->addFinancePosition(
            $em,
            $salesCompany,
            $excludePermissions
        );

        $em->flush();
    }

    /**
     * @param $em
     * @param $salesCompany
     * @param $excludePermissions
     */
    private function addGeneralManagerPosition(
        $em,
        $salesCompany,
        $excludePermissions
    ) {
        $defaultGroups = array();
        $defaultPermissions = array();

        $generalManagerGroups = [
            AdminPermissionGroups::GROUP_KEY_DASHBOARD,
            AdminPermissionGroups::GROUP_KEY_TRADE,
            AdminPermissionGroups::GROUP_KEY_SPACE,
            AdminPermissionGroups::GROUP_KEY_USER,
            AdminPermissionGroups::GROUP_KEY_ADMIN,
            AdminPermissionGroups::GROUP_KEY_FINANCE,
        ];

        $generalManagerPermissions = [
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_CUSTOMER,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_REQUEST_INVOICE,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_MONTHLY_BILLS,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_FINANCIAL_SUMMARY,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_WITHDRAWAL,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_ACCOUNT,
                'op_level' => 2,
            ],
        ];

        $excludePermissionsKeyArray = array();
        foreach ($excludePermissions as $excludePermission) {
            if (!$excludePermission) {
                continue;
            }
            array_push($excludePermissionsKeyArray, $excludePermission['group_key']);
        }

        if (!in_array(AdminPermissionGroups::GROUP_KEY_EVENT, $excludePermissionsKeyArray)) {
            array_push($defaultPermissions,
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT,
                    'op_level' => 2,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER,
                    'op_level' => 1,
                ]
            );

            array_push($defaultGroups, AdminPermissionGroups::GROUP_KEY_EVENT);
        }

        if (!in_array(AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD, $excludePermissionsKeyArray)) {
            array_push($defaultPermissions,
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD,
                    'op_level' => 2,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER,
                    'op_level' => 1,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_PRODUCT,
                    'op_level' => 2,
                ]
            );

            array_push($defaultGroups, AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD);
        }

        $generalManagerGroups = array_merge($generalManagerGroups, $defaultGroups);
        $generalManagerPermissions = array_merge($generalManagerPermissions, $defaultPermissions);

        $this->addPosition(
            $em,
            $generalManagerPermissions,
            $generalManagerGroups,
            $salesCompany,
            '项目总经理'
        );
    }

    /**
     * @param $em
     * @param $salesCompany
     * @param $excludePermissions
     */
    private function addManagerPosition(
        $em,
        $salesCompany,
        $excludePermissions
    ) {
        $defaultGroups = array();
        $defaultPermissions = array();

        $groups = [
            AdminPermissionGroups::GROUP_KEY_DASHBOARD,
            AdminPermissionGroups::GROUP_KEY_TRADE,
            AdminPermissionGroups::GROUP_KEY_SPACE,
            AdminPermissionGroups::GROUP_KEY_USER,
            AdminPermissionGroups::GROUP_KEY_ADMIN,
            AdminPermissionGroups::GROUP_KEY_FINANCE,
        ];

        $permissions = [
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_CUSTOMER,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
                'op_level' => 1,
            ],
        ];

        $excludePermissionsKeyArray = array();
        foreach ($excludePermissions as $excludePermission) {
            if (!$excludePermission) {
                continue;
            }
            array_push($excludePermissionsKeyArray, $excludePermission['group_key']);
        }

        if (!in_array(AdminPermissionGroups::GROUP_KEY_EVENT, $excludePermissionsKeyArray)) {
            array_push($defaultPermissions,
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT,
                    'op_level' => 2,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER,
                    'op_level' => 1,
                ]
            );

            array_push($defaultGroups, AdminPermissionGroups::GROUP_KEY_EVENT);
        }

        if (!in_array(AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD, $excludePermissionsKeyArray)) {
            array_push($defaultPermissions,
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD,
                    'op_level' => 2,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER,
                    'op_level' => 1,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_PRODUCT,
                    'op_level' => 2,
                ]
            );

            array_push($defaultGroups, AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD);
        }

        $groups = array_merge($groups, $defaultGroups);
        $permissions = array_merge($permissions, $defaultPermissions);

        $this->addPosition(
            $em,
            $permissions,
            $groups,
            $salesCompany,
            '项目经理'
        );
    }

    /**
     * @param $em
     * @param $salesCompany
     * @param $excludePermissions
     */
    private function addStaffPosition(
        $em,
        $salesCompany,
        $excludePermissions
    ) {
        $defaultGroups = array();
        $defaultPermissions = array();

        $groups = [
            AdminPermissionGroups::GROUP_KEY_DASHBOARD,
            AdminPermissionGroups::GROUP_KEY_TRADE,
            AdminPermissionGroups::GROUP_KEY_SPACE,
            AdminPermissionGroups::GROUP_KEY_USER,
            AdminPermissionGroups::GROUP_KEY_ADMIN,
        ];

        $permissions = [
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_CUSTOMER,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
                'op_level' => 1,
            ],
        ];

        $excludePermissionsKeyArray = array();
        foreach ($excludePermissions as $excludePermission) {
            if (!$excludePermission) {
                continue;
            }
            array_push($excludePermissionsKeyArray, $excludePermission['group_key']);
        }

        if (!in_array(AdminPermissionGroups::GROUP_KEY_EVENT, $excludePermissionsKeyArray)) {
            array_push($defaultPermissions,
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT,
                    'op_level' => 2,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER,
                    'op_level' => 1,
                ]
            );

            array_push($defaultGroups, AdminPermissionGroups::GROUP_KEY_EVENT);
        }

        if (!in_array(AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD, $excludePermissionsKeyArray)) {
            array_push($defaultPermissions,
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD,
                    'op_level' => 1,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER,
                    'op_level' => 1,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_PRODUCT,
                    'op_level' => 1,
                ]
            );

            array_push($defaultGroups, AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD);
        }

        $groups = array_merge($groups, $defaultGroups);
        $permissions = array_merge($permissions, $defaultPermissions);

        $this->addPosition(
            $em,
            $permissions,
            $groups,
            $salesCompany,
            '员工'
        );
    }

    /**
     * @param $em
     * @param $salesCompany
     * @param $excludePermissions
     */
    private function addFinancePosition(
        $em,
        $salesCompany,
        $excludePermissions
    ) {
        $defaultGroups = array();
        $defaultPermissions = array();

        $groups = [
            AdminPermissionGroups::GROUP_KEY_DASHBOARD,
            AdminPermissionGroups::GROUP_KEY_TRADE,
            AdminPermissionGroups::GROUP_KEY_SPACE,
            AdminPermissionGroups::GROUP_KEY_USER,
            AdminPermissionGroups::GROUP_KEY_ADMIN,
            AdminPermissionGroups::GROUP_KEY_FINANCE,
        ];

        $permissions = [
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_CUSTOMER,
                'op_level' => 1,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_REQUEST_INVOICE,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_MONTHLY_BILLS,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_FINANCIAL_SUMMARY,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_WITHDRAWAL,
                'op_level' => 2,
            ],
            [
                'key' => AdminPermission::KEY_SALES_PLATFORM_ACCOUNT,
                'op_level' => 2,
            ],
        ];

        $excludePermissionsKeyArray = array();
        foreach ($excludePermissions as $excludePermission) {
            if (!$excludePermission) {
                continue;
            }
            array_push($excludePermissionsKeyArray, $excludePermission['group_key']);
        }

        if (!in_array(AdminPermissionGroups::GROUP_KEY_EVENT, $excludePermissionsKeyArray)) {
            array_push($defaultPermissions,
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER,
                    'op_level' => 1,
                ]
            );

            array_push($defaultGroups, AdminPermissionGroups::GROUP_KEY_EVENT);
        }

        if (!in_array(AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD, $excludePermissionsKeyArray)) {
            array_push($defaultPermissions,
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD,
                    'op_level' => 1,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER,
                    'op_level' => 1,
                ],
                [
                    'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_PRODUCT,
                    'op_level' => 1,
                ]
            );

            array_push($defaultGroups, AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD);
        }

        $groups = array_merge($groups, $defaultGroups);
        $permissions = array_merge($permissions, $defaultPermissions);

        $this->addPosition(
            $em,
            $permissions,
            $groups,
            $salesCompany,
            '财务'
        );
    }

    /**
     * @param $em
     * @param $permissions
     * @param $groups
     * @param $salesCompany
     * @param $positionName
     */
    private function addPosition(
        $em,
        $permissions,
        $groups,
        $salesCompany,
        $positionName
    ) {
        $icon = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')->find(1);

        $positionGeneralManager = new AdminPosition();
        $positionGeneralManager->setName($positionName);
        $positionGeneralManager->setPlatform(AdminPosition::PLATFORM_SALES);
        $positionGeneralManager->setSalesCompany($salesCompany);
        $positionGeneralManager->setIcon($icon);
        $em->persist($positionGeneralManager);

        foreach ($permissions as $permissionArray) {
            $permission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findOneBy(array(
                    'key' => $permissionArray['key'],
                ));

            if (!$permission) {
                continue;
            }

            $adminPositionPermissionMap = new AdminPositionPermissionMap();
            $adminPositionPermissionMap->setPosition($positionGeneralManager);
            $adminPositionPermissionMap->setPermission($permission);
            $adminPositionPermissionMap->setOpLevel($permissionArray['op_level']);
            $em->persist($adminPositionPermissionMap);
        }

        foreach ($groups as $generalManagerGroup) {
            $group = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
                ->findOneBy(array(
                    'groupKey' => $generalManagerGroup,
                    'platform' => AdminPermissionGroups::GROUP_PLATFORM_SALES,
                ));

            if (!$group) {
                continue;
            }

            $adminPositionGroupMap = new AdminPositionGroupBinding();
            $adminPositionGroupMap->setGroup($group);
            $adminPositionGroupMap->setPosition($positionGeneralManager);
            $em->persist($adminPositionGroupMap);
        }
    }
}
