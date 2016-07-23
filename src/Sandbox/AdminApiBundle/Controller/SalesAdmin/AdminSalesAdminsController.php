<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminExcludePermission;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesAdminPostType;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesAdminPutType;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Doctrine\ORM\EntityManager;

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
class AdminSalesAdminsController extends SandboxRestController
{
    const ERROR_USERNAME_INVALID_CODE = 400001;
    const ERROR_USERNAME_INVALID_MESSAGE = 'Invalid username - 无效的用户名';

    const ERROR_USERNAME_EXIST_CODE = 400002;
    const ERROR_USERNAME_EXIST_MESSAGE = 'Username already exist - 用户名已被占用';

    const ERROR_PASSWORD_INVALID_CODE = 400003;
    const ERROR_PASSWORD_INVALID_MESSAGE = 'Invalid password - 无效的密码';

    const ERROR_ADMIN_TYPE_CODE = 400004;
    const ERROR_ADMIN_TYPE_MESSAGE = 'Invalid admin type - 无效的管理员类型';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Annotations\QueryParam(
     *    name="username",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="sales admin username"
     * )
     *
     * @Route("/admins/check")
     * @Method({"GET"})
     *
     * @return View
     */
    public function checkAdminUsernameValidAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        $salesAdminUsername = $paramFetcher->get('username');

        $salesAdmin = $this->getRepo('SalesAdmin\SalesAdmin')->findOneByUsername($salesAdminUsername);

        if (!is_null($salesAdmin)) {
            return $this->customErrorView(
                400,
                self::ERROR_USERNAME_EXIST_CODE,
                self::ERROR_USERNAME_EXIST_MESSAGE
            );
        }

        return new View();
    }

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
     * @Route("/admins")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getSalesAdminsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $banned = $paramFetcher->get('banned');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $search = $paramFetcher->get('query');

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        // get by admin type
        $typeKey = SalesAdminType::KEY_SUPER;
        $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey($typeKey);

        // get type id
        $typeId = $type->getId();

        $admins = $this->getRepo('SalesAdmin\SalesAdmin')->getSalesAdmins(
            $typeId,
            $banned,
            $search
        );

        if (!is_null($admins) && !empty($admins)) {
            foreach ($admins as $admin) {
                $buildingCounts = $this->getRepo('Room\RoomBuilding')->countSalesBuildings($admin->getCompanyId());
                $shopAdminCounts = $this->getRepo('Shop\ShopAdmin')->countShopAdmins($admin->getCompanyId());
                $shops = $this->getRepo('Shop\Shop')->getShopsByCompany($admin->getCompanyId());
                $shopCounts = count($shops);

                $admin->setShopAdminCounts((int) $shopAdminCounts);
                $admin->setBuildingCounts((int) $buildingCounts);
                $admin->setShopCounts((int) $shopCounts);

                // new pending building
                $pendingBuilding = $this->getRepo('Room\RoomBuilding')->findOneBy(array(
                    'companyId' => $admin->getCompanyId(),
                    'status' => RoomBuilding::STATUS_PENDING,
                    'isDeleted' => false,
                ));
                if (!is_null($pendingBuilding)) {
                    $admin->setHasPendingBuilding(true);
                }

                // new pending shop
                foreach ($shops as $shop) {
                    if (!$shop->isActive() && !$shop->isDeleted()) {
                        $admin->setHasPendingShop(true);
                    }
                }
            }
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $admins,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * List definite id of admin.
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
     * @Route("/admins/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        // get admin
        $admin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findOneBy(array('id' => $id));

        // select super admin permissions without auto ORM
        if ($admin->getType()->getKey() == SalesAdminType::KEY_SUPER) {
            $permissions = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminPermission')
                ->getSalesAdminPermissions($admin->getCompanyId());

            $adminJson = $this->container->get('serializer')->serialize($admin, 'json');
            $adminArray = json_decode($adminJson, true);
            $adminArray['permissions'] = $permissions;

            $admin = $adminArray;
        }

        // set view
        $view = new View($admin);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('admin'))
        );

        return $view;
    }

    /**
     * Create admin.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Method({"POST"})
     * @Route("/admins")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminsAction(
        Request $request
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        // bind admin data
        $admin = new SalesAdmin();
        $form = $this->createForm(new SalesAdminPostType(), $admin);
        $form->handleRequest($request);

        $typeKey = $form['type_key']->getData();
        $company = $form['company']->getData();
        $excludePermissions = $form['exclude_permissions']->getData();

        if (is_null($company)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if ($form->isValid()) {
            return $this->handleAdminCreate(
                $admin,
                $typeKey,
                $company,
                $excludePermissions
            );
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * Update Admin.
     *
     * @param Request $request the request object
     * @param int     $id      the admin ID
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     *
     * @Route("/admins/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchAdminAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        $admin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')->find($id);
        $this->throwNotFoundIfNull($admin, self::NOT_FOUND_MESSAGE);

        $passwordOld = $admin->getPassword();
        $bannedOld = $admin->isBanned();
        $usernameOrigin = $admin->getUsername();

        // bind data
        $adminJson = $this->container->get('serializer')->serialize($admin, 'json');
        $patch = new Patch($adminJson, $request->getContent());
        $adminJson = $patch->apply();

        $form = $this->createForm(new SalesAdminPutType(), $admin);
        $form->submit(json_decode($adminJson, true));

        $passwordNew = $admin->getPassword();
        if ($passwordOld != $passwordNew) {
            $admin->setDefaultPasswordChanged(false);
        }

        $type_key = $form['type_key']->getData();
        $company = $form['company']->getData();
        $excludePermissions = $form['exclude_permissions']->getData();

        // handle admin banned
        $this->handleAdminBanned(
            $bannedOld,
            $admin
        );

        return $this->handleAdminPatch(
            $admin,
            $type_key,
            $company,
            $passwordOld,
            $usernameOrigin,
            $excludePermissions
        );
    }

    /**
     * @param bool       $bannedOld
     * @param SalesAdmin $admin
     */
    private function handleAdminBanned(
        $bannedOld,
        $admin
    ) {
        $companyId = $admin->getCompanyId();
        $banned = $admin->isBanned();

        if ($bannedOld == $banned) {
            return;
        }

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES,
            AdminPermissionMap::OP_LEVEL_USER_BANNED
        );

        try {
            if ($banned) {
                $this->handleSalesAndShopsByCompany(
                    $companyId,
                    true,
                    false,
                    RoomBuilding::STATUS_BANNED,
                    false
                );
            } else {
                $this->handleSalesAndShopsByCompany(
                    $companyId,
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
     * @param SalesAdmin     $admin
     * @param SalesAdminType $typeKey
     * @param SalesCompany   $company
     * @param string         $passwordOrigin
     * @param string         $usernameOrigin
     * @param array          $excludePermissions
     *
     * @return View
     */
    private function handleAdminPatch(
        $admin,
        $typeKey,
        $company,
        $passwordOrigin,
        $usernameOrigin,
        $excludePermissions
    ) {
        $em = $this->getDoctrine()->getManager();
        if (!is_null($typeKey)) {
            $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey($typeKey);
            $admin->setTypeId($type->getId());
        }
        $em->persist($admin);

        // set sales company
        if (!is_null($company) || !empty($company)) {
            $salesCompany = $this->getRepo('SalesAdmin\SalesCompany')->find($admin->getCompanyId());
            $form = $this->createForm(new SalesCompanyPostType(), $salesCompany);
            $form->submit($company);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        // set admin exclude permissions
        $this->saveExcludePermissions(
            $em,
            $excludePermissions,
            $admin->getSalesCompany()
        );

        //save data
        $em->flush();

        if ($usernameOrigin != $admin->getUsername()
            || $passwordOrigin != $admin->getPassword()
        ) {
            // logout this admin
            $this->getRepo('SalesAdmin\SalesAdminToken')->deleteSalesAdminToken(
                $admin->getId()
            );
        }

        return new View();
    }

    /**
     * @param SalesAdmin     $admin
     * @param SalesAdminType $typeKey
     * @param SalesCompany   $company
     * @param array          $excludePermissions
     *
     * @return View
     */
    private function handleAdminCreate(
        $admin,
        $typeKey,
        $company,
        $excludePermissions
    ) {
        $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey($typeKey);
        $admin->setType($type);
        $admin->setTypeId($type->getId());

        $checkAdminValid = $this->checkAdminValid($admin);
        if (!is_null($checkAdminValid)) {
            return $checkAdminValid;
        }

        // save admin to db
        $admin = $this->saveAdmin(
            $admin,
            $company,
            $excludePermissions
        );

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $admin->getId(),
        ));

        return $view;
    }

    /**
     * @param SalesAdmin   $admin
     * @param SalesCompany $company
     * @param array        $excludePermissions
     *
     * @return SalesAdmin
     */
    private function saveAdmin(
        $admin,
        $company,
        $excludePermissions
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        // set sales company
        $salesCompany = new SalesCompany();
        $form = $this->createForm(new SalesCompanyPostType(), $salesCompany);
        $form->submit($company);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $admin->setSalesCompany($salesCompany);

        // set admin exclude permissions
        $this->saveExcludePermissions(
            $em,
            $excludePermissions,
            $salesCompany
        );

        // set dates
        $salesCompany->setCreationDate($now);
        $salesCompany->setModificationDate($now);

        $admin->setCreationDate($now);
        $admin->setModificationDate($now);

        // save admin
        $em->persist($salesCompany);
        $em->persist($admin);
        $em->flush();

        return $admin;
    }

    /**
     * @param SalesAdmin $admin
     *
     * @return View
     */
    private function checkAdminValid(
        $admin
    ) {
        // check username
        if (is_null($admin->getUsername())) {
            return $this->customErrorView(
                400,
                self::ERROR_USERNAME_INVALID_CODE,
                self::ERROR_PASSWORD_INVALID_MESSAGE);
        }

        // check username exist
        $adminExist = $this->getRepo('SalesAdmin\SalesAdmin')->findOneByUsername($admin->getUsername());
        if (!is_null($adminExist)) {
            return $this->customErrorView(
                400,
                self::ERROR_USERNAME_EXIST_CODE,
                self::ERROR_USERNAME_EXIST_MESSAGE);
        }

        // check password
        if (is_null($admin->getPassword())) {
            return $this->customErrorView(
                400,
                self::ERROR_PASSWORD_INVALID_CODE,
                self::ERROR_PASSWORD_INVALID_MESSAGE);
        }

        // check admin type
        if (is_null($admin->getTypeId())) {
            return $this->customErrorView(
                400,
                self::ERROR_ADMIN_TYPE_CODE,
                self::ERROR_ADMIN_TYPE_MESSAGE);
        } else {
            $type = $this->getRepo('SalesAdmin\SalesAdminType')->find($admin->getTypeId());
            if (is_null($type)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_ADMIN_TYPE_CODE,
                    self::ERROR_ADMIN_TYPE_MESSAGE);
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
        // set banned sales admins that belong to this company
        $salesPlatformAdmins = $this->getRepo('SalesAdmin\SalesAdmin')->findByCompanyId($companyId);
        if (!empty($salesPlatformAdmins)) {
            foreach ($salesPlatformAdmins as $platformAdmin) {
                $platformAdmin->setBanned($platformAdminBanned);

                // logout this admin
                if ($platformAdminBanned) {
                    $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminToken')
                        ->deleteSalesAdminToken(
                            $platformAdmin->getId()
                        );
                }
            }
        }

        // set banned shop admins that belong to this company
        $shopPlatformAdmins = $this->getRepo('Shop\ShopAdmin')->findByCompanyId($companyId);
        if (!empty($shopPlatformAdmins)) {
            foreach ($shopPlatformAdmins as $platformAdmin) {
                $platformAdmin->setBanned($platformAdminBanned);

                // logout this admin
                if ($platformAdminBanned) {
                    $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Shop\ShopAdminToken')
                        ->deleteShopAdminToken(
                            $platformAdmin->getId()
                        );
                }
            }
        }

        // set buildings visible and status
        $buildings = $this->getRepo('Room\RoomBuilding')->findByCompanyId($companyId);

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
            $shops = $this->getRepo('Shop\Shop')->findByBuilding($building);

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
                    $this->getRepo('Shop\ShopProduct')->setShopProductsOfflineByShopId(
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
        $products = $this->getRepo('Product\Product')->getSalesProductsByBuilding($building);

        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {
            $product->setVisible(false);
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
        // check input data, "null" means do not set exclude permission
        if (is_null($excludePermissions)) {
            return;
        }

        // remove old data
        $excludePermissionsRemove = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminExcludePermission')
            ->findBy(array(
                'salesCompany' => $salesCompany,
            ));
        if (!empty($excludePermissionsRemove)) {
            foreach ($excludePermissionsRemove as $excludePermission) {
                $em->remove($excludePermission);
            }
        }
        $em->flush();

        // check input data, "empty" means set all permissions include
        if (empty($excludePermissions)) {
            return;
        }

        foreach ($excludePermissions as $excludePermission) {
            if (!array_key_exists('key', $excludePermission)) {
                continue;
            }

            $permission = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminPermission')
                ->findOneBy(array(
                    'key' => $excludePermission['key'],
                ));

            $excludePermissionEm = new SalesAdminExcludePermission();
            $excludePermissionEm->setSalesCompany($salesCompany);
            $excludePermissionEm->setPermission($permission);

            $em->persist($excludePermissionEm);
        }
    }
}
