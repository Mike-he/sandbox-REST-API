<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminExcludePermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionUserBinding;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyPatchType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesCompanyPostType;
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
    const POSITION_ADMIN = '超级管理员';
    const POSITION_COFFEE_ADMIN = '超级管理员';

    const ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_CODE = 400005;
    const ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_MESSAGE = 'Over the super administrator limit number';
    const ERROR_NOT_NULL_SUPER_ADMIN_CODE = 400006;
    const ERROR_NOT_NULL_SUPER_ADMIN_MESSAGE = 'Must at least one super administrator position binding';

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
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_VIEW);

        $salesCompanies = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->getCompanyList(
                $banned,
                $search
            );

        if (!is_null($salesCompanies) && !empty($salesCompanies)) {
            foreach ($salesCompanies as $company) {
                $buildingCounts = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')->countSalesBuildings($company);
                $shops = $this->getDoctrine()->getRepository('SandboxApiBundle:Shop\Shop')->getShopsByCompany($company);
                $shopCounts = count($shops);

                $adminPosition = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPosition')
                    ->findOneBy(
                        array(
                            'salesCompany' => $company,
                            'name' => self::POSITION_ADMIN,
                            'platform' => AdminPermission::PERMISSION_PLATFORM_SALES,
                            'isSuperAdmin' => true,
                        )
                    );
                $adminPositionUser = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->findBy(array('position' => $adminPosition));

                $coffeeAdminPosition = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPosition')
                    ->findOneBy(
                        array(
                            'salesCompany' => $company,
                            'name' => self::POSITION_COFFEE_ADMIN,
                            'platform' => AdminPermission::PERMISSION_PLATFORM_SHOP,
                            'isSuperAdmin' => true,
                        )
                    );

                $coffeeAdminPositionUser = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->findBy(array('position' => $coffeeAdminPosition));

                $company->setAdmin($adminPositionUser);
                $company->setCoffeeAdmin($coffeeAdminPositionUser);
                $company->setBuildingCounts((int) $buildingCounts);
                $company->setShopCounts((int) $shopCounts);
            }
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $salesCompanies,
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
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_VIEW);

        $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($id);
        if (is_null($company)) {
            return new View();
        }

        $adminPosition = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(
                array(
                    'salesCompany' => $company,
                    'name' => self::POSITION_ADMIN,
                    'platform' => AdminPermission::PERMISSION_PLATFORM_SALES,
                    'isSuperAdmin' => true,
                )
            );

        $admin = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findBy(array('position' => $adminPosition));
        $company->setAdmin($admin);

        $coffeeAdminPosition = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(
                array(
                    'salesCompany' => $company,
                    'name' => self::POSITION_COFFEE_ADMIN,
                    'platform' => AdminPermission::PERMISSION_PLATFORM_SHOP,
                    'isSuperAdmin' => true,
                )
            );

        $coffeeAdmin = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findOneBy(array('position' => $coffeeAdminPosition));
        $company->setCoffeeAdmin($coffeeAdmin);

        // set view
        $view = new View($company);

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
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_EDIT);

        $userId = $request->get('user_id');
        $company = $request->get('company');
        $excludePermissions = $request->get('exclude_permissions');

        if (is_null($company)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
        if (is_null($user)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
        $salesCompany = $this->saveAdmin(
            $user,
            $company,
            $excludePermissions
        );

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $salesCompany->getId(),
        ));

        return $view;
    }

    /**
     * Update Admin.
     *
     * @param Request $request the request object
     * @param int     $id      the admin ID
     *
     * @Route("/admins/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putAdminAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkSalesAdminPermission(AdminPermission::OP_LEVEL_EDIT);

        if (is_null($request->get('user_ids')) || empty($request->get('user_ids'))) {
            return $this->customErrorView(
                400,
                self::ERROR_NOT_NULL_SUPER_ADMIN_CODE,
                self::ERROR_NOT_NULL_SUPER_ADMIN_MESSAGE
            );
        }

        $userIds = explode(',', $request->get('user_ids'));
        if (count($userIds) > 2) {
            return $this->customErrorView(
                400,
                self::ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_CODE,
                self::ERROR_OVER_LIMIT_SUPER_ADMIN_NUMBER_MESSAGE
            );
        }

        $company = $request->get('company');
        $excludePermissions = $request->get('exclude_permissions');

        $salesCompany = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($id);
        $this->throwNotFoundIfNull($salesCompany, self::NOT_FOUND_MESSAGE);

        $this->handleAdminPut(
            $salesCompany,
            $userIds,
            $company,
            $excludePermissions
        );
    }

    /**
     * Update Admin.
     *
     * @param Request $request the request object
     * @param int     $id      the admin ID
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
     * @param $salesCompany
     * @param $userIds
     * @param $company
     * @param $excludePermissions
     *
     * @return View
     */
    private function handleAdminPut(
        $salesCompany,
        $userIds,
        $company,
        $excludePermissions
    ) {
        $em = $this->getDoctrine()->getManager();

        // set sales company
        if (!is_null($company) || !empty($company)) {
            $form = $this->createForm(new SalesCompanyPostType(), $salesCompany);
            $form->submit($company);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }
        $now = new \DateTime('now');
        $salesCompany->setModificationDate($now);

        $this->updatePosition(
            $salesCompany,
            $userIds
        );

        // set admin exclude permissions
        $this->saveExcludePermissions(
            $em,
            $excludePermissions,
            $salesCompany
        );

        //save data
        $em->flush();

        return new View();
    }

    /**
     * @param $user
     * @param $company
     * @param $excludePermissions
     *
     * @return SalesCompany
     */
    private function saveAdmin(
        $user,
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
        $salesCompany->setCreationDate($now);
        $salesCompany->setModificationDate($now);
        $em->persist($salesCompany);

        $position = $this->createPosition(
            $user,
            $salesCompany,
            self::POSITION_ADMIN
        );

        // set admin exclude permissions
        $this->saveExcludePermissions(
            $em,
            $excludePermissions,
            $salesCompany
        );

        $em->flush();

        return $salesCompany;
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
        $name
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime('now');

        $icon = $em->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')->find(1);

        $position = new AdminPosition();
        $position->setName($name);
        $position->setPlatform(AdminPermission::PERMISSION_PLATFORM_SALES);
        $position->setIsSuperAdmin(true);
        $position->setIcon($icon);
        $position->setSalesCompany($salesCompany);
        $position->setCreationDate($now);
        $position->setModificationDate($now);
        $em->persist($position);

        $adminPositionUser = new AdminPositionUserBinding();
        $adminPositionUser->setUser($user);
        $adminPositionUser->setPosition($position);
        $adminPositionUser->setCreationDate($now);
        $em->persist($adminPositionUser);

        return $position;
    }

    /**
     * @param $company
     * @param $userIds
     */
    private function updatePosition(
        $company,
        $userIds
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime('now');

        $adminPosition = $em->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(
                array(
                    'salesCompany' => $company,
                    'name' => self::POSITION_ADMIN,
                    'platform' => AdminPermission::PERMISSION_PLATFORM_SALES,
                    'isSuperAdmin' => true,
                )
            );

        $adminPositionUsers = $em->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findBy(array('position' => $adminPosition));

        foreach ($adminPositionUsers as $adminPositionUser) {
            $em->remove($adminPositionUser);
            $em->flush();
        }

        foreach ($userIds as $userId) {
            $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
            if (!is_null($user)) {
                $adminPositionUser = new AdminPositionUserBinding();
                $adminPositionUser->setUser($user);
                $adminPositionUser->setPosition($adminPosition);
                $adminPositionUser->setCreationDate($now);
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
            ->getRepository('SandboxApiBundle:Admin\AdminExcludePermission')
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
}
