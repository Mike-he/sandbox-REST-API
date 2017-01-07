<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminExcludePermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionUserBinding;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyPostType;
use Sandbox\ApiBundle\Form\SalesAdmin\ServiceInfoPostType;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
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
 * @link     http://www.Sandbox.cn/
 */
class AdminSalesCompanyController extends SandboxRestController
{
    const POSITION_ADMIN = '超级管理员';
    const POSITION_COFFEE_ADMIN = '超级管理员';

    const ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_CODE = 400005;
    const ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_MESSAGE = 'Over the super administrator limit number';
    const ERROR_NOT_NULL_SUPER_ADMIN_CODE = 400006;
    const ERROR_NOT_NULL_SUPER_ADMIN_MESSAGE = 'Must at least one super administrator position binding';

    use HasAccessToEntityRepositoryTrait;

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
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="banned",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="sales admin banned status"
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
     *    name="query",
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
        $banned = $paramFetcher->get('banned');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $search = $paramFetcher->get('query');

        // check user permission
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_VIEW);

        $salesCompanies = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->getCompanyList(
                $banned,
                $search
            );

        foreach ($salesCompanies as $company) {
            $buildingCounts = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->countSalesBuildings($company);
            $shops = $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\Shop')->getShopsByCompany($company);
            $shopCounts = count($shops);

            $company->setBuildingCounts((int) $buildingCounts);
            $company->setShopCounts((int) $shopCounts);

            // new pending building
            $pendingBuilding = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->findOneBy(array(
                    'companyId' => $company,
                    'status' => RoomBuilding::STATUS_PENDING,
                    'isDeleted' => false,
                ));
            if (!is_null($pendingBuilding)) {
                $company->setHasPendingBuilding(true);
            }

            // new pending shop
            foreach ($shops as $shop) {
                if (!$shop->isActive() && !$shop->isDeleted()) {
                    $company->setHasPendingShop(true);
                }
            }

            // check event module
            $permission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findOneBy([
                    'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT,
                ]);
            if (!is_null($permission)) {
                $excludePermission = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminExcludePermission')
                    ->findOneBy([
                        'salesCompany' => $company,
                        'permission' => $permission,
                    ]);
                if (is_null($excludePermission)) {
                    $company->setHasEventModule(true);
                }
            }
        }

        $salesCompanies = $this->get('serializer')->serialize(
            $salesCompanies,
            'json',
            SerializationContext::create()->setGroups(['admin_list'])
        );
        $salesCompanies = json_decode($salesCompanies, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $salesCompanies,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * List definite id of company.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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

            array_push($coffeeUserArray, $coffeeUser);
        }

        $company->setCoffeeAdmins($coffeeUserArray);

        // building and shop counts
        $buildingCounts = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->countSalesBuildings($company);
        $shops = $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\Shop')->getShopsByCompany($company);
        $shopCounts = count($shops);

        $company->setBuildingCounts((int) $buildingCounts);
        $company->setShopCounts((int) $shopCounts);

        // new pending building
        $pendingBuilding = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findOneBy(array(
                'companyId' => $company,
                'status' => RoomBuilding::STATUS_PENDING,
                'isDeleted' => false,
            ));
        if (!is_null($pendingBuilding)) {
            $company->setHasPendingBuilding(true);
        }

        // new pending shop
        foreach ($shops as $shop) {
            if (!$shop->isActive() && !$shop->isDeleted()) {
                $company->setHasPendingShop(true);
            }
        }

        // check event module
        $permission = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy([
                'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT,
            ]);
        if (!is_null($permission)) {
            $excludePermission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminExcludePermission')
                ->findOneBy([
                    'salesCompany' => $company,
                    'permission' => $permission,
                ]);
            if (is_null($excludePermission)) {
                $company->setHasEventModule(true);
            }
        }

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
     * Create sales company.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful"
     *  }
     * )
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

        $em->flush();

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

        $salesCompany = $this->getSalesCompanyRepo()->find($id);
        $this->throwNotFoundIfNull($salesCompany, CustomErrorMessagesConstants::ERROR_SALES_COMPANY_NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();

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
                'position' => $position
            ]);

        if (is_null($binding)) {
            $adminPositionUser = new AdminPositionUserBinding();
            $adminPositionUser->setUser($user);
            $adminPositionUser->setPosition($position);
            $em->persist($adminPositionUser);
        }
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
                    'position' => $position
                ]);

            if (is_null($binding)) {
                $adminPositionUser = new AdminPositionUserBinding();
                $adminPositionUser->setUser($user);
                $adminPositionUser->setPosition($position);
                $em->persist($adminPositionUser);
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
        if ($status == false) {
            $products = $this->getProductRepo()->findProductsByType(
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
            if (!array_key_exists('key', $excludePermission)) {
                continue;
            }

            $permission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findOneBy(array(
                    'key' => $excludePermission['key'],
                ));

            $excludePermissionEm = new AdminExcludePermission();
            $excludePermissionEm->setSalesCompany($salesCompany);
            $excludePermissionEm->setPermission($permission);
            $excludePermissionEm->setPlatform(AdminPosition::PLATFORM_SALES);

            $em->persist($excludePermissionEm);
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
        $this->throwAccessDeniedIfAdminNotAllowed(
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
            $admin = $this->getUserRepo()->find($adminId);
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
            $service = $this->getSalesCompanyServiceInfosRepo()
                ->findOneBy(
                    array(
                        'roomTypes' => $serviceInfo['room_types'],
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
                    $service->getRoomTypes()
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
}
