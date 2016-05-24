<?php

namespace Sandbox\AdminShopApiBundle\Controller\Admin;

use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Shop\ShopAdmin;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sandbox\ApiBundle\Form\Shop\ShopPlatformAdminPostType;
use Sandbox\ApiBundle\Form\Shop\ShopMyAdminPutType;
use Sandbox\ApiBundle\Form\Shop\ShopPlatformAdminPutType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;

/**
 * ShopAdmin controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAdminsController extends ShopRestController
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
        $this->checkAdminAdminsPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_PLATFORM_ADMIN,
            )
        );

        $shopAdminUsername = $paramFetcher->get('username');

        $shopAdmin = $this->getRepo('Shop\ShopAdmin')->findOneByUsername($shopAdminUsername);

        if (!is_null($shopAdmin)) {
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
     *    name="type",
     *    array=false,
     *    default="platform",
     *    nullable=true,
     *    requirements="(super | platform)",
     *    strict=true,
     *    description="admin types"
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
    public function getShopAdminsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminAdminsPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_PLATFORM_ADMIN,
            )
        );

        $typeKey = $paramFetcher->get('type');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        // get all admins id and username
        $type = $this->getRepo('Shop\ShopAdminType')->findOneByKey($typeKey);
        $companyId = $this->getUser()->getMyAdmin()->getCompanyId();
        $query = $this->getRepo('Shop\ShopAdmin')->findBy(array(
            'typeId' => $type->getId(),
            'companyId' => $companyId,
        ));

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
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
        $this->checkAdminAdminsPermission(
            ShopAdminPermissionMap::OP_LEVEL_VIEW,
            array(
                ShopAdminPermission::KEY_PLATFORM_ADMIN,
            )
        );

        // get all admins
        $type = $this->getRepo('Shop\ShopAdminType')->findOneByKey(AdminType::KEY_PLATFORM);
        $companyId = $this->getUser()->getMyAdmin()->getCompanyId();
        $admins = $this->getRepo('Shop\ShopAdmin')->findOneBy(array(
            'id' => $id,
            'companyId' => $companyId,
            'typeId' => $type->getId(),
        ));

        // set building id and city id
        $permissions = $admins->getPermissions();
        foreach ($permissions as $permission) {
            $shopId = $permission->getShopId();
            if (is_null($shopId)) {
                continue;
            }

            $shop = $this->getRepo('Shop\Shop')->find($shopId);

            if (is_null($shop)) {
                continue;
            }

            $permission->setShop($shop);
        }

        // set view
        $view = new View($admins);
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
        $this->checkAdminAdminsPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_PLATFORM_ADMIN,
            )
        );

        // bind admin data
        $admin = new ShopAdmin();
        $form = $this->createForm(new ShopPlatformAdminPostType(), $admin);
        $form->handleRequest($request);

        $type_key = $form['type_key']->getData();
        $permission = $form['permission']->getData();

        if ($form->isValid()) {
            return $this->handleAdminCreate(
                $admin,
                $type_key,
                $permission
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
        $this->checkAdminAdminsPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_PLATFORM_ADMIN,
            )
        );

        $admin = $this->getRepo('Shop\ShopAdmin')->find($id);
        $passwordOld = $admin->getPassword();

        // bind data
        $adminJson = $this->container->get('serializer')->serialize($admin, 'json');
        $patch = new Patch($adminJson, $request->getContent());
        $adminJson = $patch->apply();

        $form = $this->createForm(new ShopPlatformAdminPutType(), $admin);
        $form->submit(json_decode($adminJson, true));

        $passwordNew = $admin->getPassword();
        if ($passwordOld != $passwordNew) {
            $admin->setDefaultPasswordChanged(false);
        }

        $type_key = $form['type_key']->getData();
        $permission = $form['permission']->getData();

        return $this->handleAdminPatch(
            $id,
            $admin,
            $type_key,
            $permission
        );
    }

    /**
     * Update my admin.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/admins/password")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchMyAdminAction(
        Request $request
    ) {
        $admin = $this->getUser()->getMyAdmin();

        $passwordOld = $admin->getPassword();

        // bind data
        $adminData = json_decode($request->getContent(), true);

        $form = $this->createForm(new ShopMyAdminPutType(), $admin);
        $form->submit($adminData);

        $passwordNew = $admin->getPassword();

        $passwordOldPut = $form['old_password']->getData();
        if ($passwordOldPut != $passwordOld || $passwordNew == $passwordOld || is_null($passwordNew)) {
            return $this->customErrorView(
                400,
                self::ERROR_PASSWORD_INVALID_CODE,
                self::ERROR_PASSWORD_INVALID_MESSAGE
            );
        }

        // set default password change true
        $admin->setDefaultPasswordChanged(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/admins/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteAdminAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdminsPermission(
            ShopAdminPermissionMap::OP_LEVEL_EDIT,
            array(
                ShopAdminPermission::KEY_PLATFORM_ADMIN,
            )
        );

        $myAdminId = $this->getAdminId();

        // get admin
        $admin = $this->getRepo('Shop\ShopAdmin')->find($id);

        if (!is_null($admin)) {
            // check admin is myself
            if ($myAdminId == $admin->getId()) {
                throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            // remove from db
            $em = $this->getDoctrine()->getManager();
            $em->remove($admin);
            $em->flush();
        }

        return new View();
    }

    /**
     * @param ShopAdmin              $admin
     * @param ShopAdmin              $id
     * @param ShopAdminType          $typeKey
     * @param ShopAdminPermissionMap $permissionInComing
     *
     * @return View
     */
    private function handleAdminPatch(
        $id,
        $admin,
        $typeKey,
        $permissionInComing
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime('now');

        if (!is_null($typeKey)) {
            $type = $this->getRepo('Shop\ShopAdminType')->findOneByKey($typeKey);
            $admin->setTypeId($type->getId());
        }
        $em->persist($admin);

        // logout this admin
        $this->getRepo('ShopAdmin\ShopAdminToken')->deleteShopAdminToken(
            $admin->getId()
        );

        if (is_null($permissionInComing) || empty($permissionInComing)) {
            // save data
            $em->flush();

            return new View();
        }

        $permissionInComingArray = array();

        // check incoming permissions in db
        foreach ($permissionInComing as $item) {
            $shopId = array_key_exists('shop_id', $item) ? $item['shop_id'] : null;

            // get permission
            $permission = $this->getRepo('Shop\ShopAdminPermission')->find($item['id']);
            if (is_null($permission)) {
                continue;
            }

            $permissionId = $permission->getId();

            // generate incoming permission id array
            array_push($permissionInComingArray, array($permissionId, $shopId));

            // get from db
            $permissionDb = $this->getRepo('Shop\ShopAdminPermissionMap')->findOneBy(array(
                'adminId' => $id,
                'permissionId' => $permissionId,
                'shopId' => $shopId,
            ));

            // not in db
            if (is_null($permissionDb)) {
                // save permission map
                $permissionMap = new ShopAdminPermissionMap();
                $permissionMap->setCreationDate($now);
                $permissionMap->setAdmin($admin);
                $permissionMap->setPermission($permission);
                $permissionMap->setOpLevel($item['op_level']);
                $permissionMap->setShopId($shopId);

                $em->persist($permissionMap);

                continue;
            }

            // opLevel change
            if ($item['op_level'] != $permissionDb->getOpLevel()) {
                $permissionDb->setOpLevel($item['op_level']);
            }
        }

        // remove permissions from db
        $permissionDbAll = $this->getRepo('Shop\ShopAdminPermissionMap')->findByAdminId($id);
        foreach ($permissionDbAll as $item) {
            $permissionArray = array(
                $item->getPermissionId(),
                $item->getShopId(),
            );

            if (!in_array($permissionArray, $permissionInComingArray)) {
                $em->remove($item);
            }
        }

        //save data
        $em->flush();

        return new View();
    }

    /**
     * @param ShopAdmin     $admin
     * @param ShopAdminType $typeKey
     * @param array         $permission
     *
     * @return View
     */
    private function handleAdminCreate(
        $admin,
        $typeKey,
        $permission
    ) {
        $type = $this->getRepo('Shop\ShopAdminType')->findOneByKey($typeKey);
        $admin->setType($type);
        $admin->setTypeId($type->getId());

        $checkAdminValid = $this->checkAdminValid($admin);
        if (!is_null($checkAdminValid)) {
            return $checkAdminValid;
        }

        // save admin to db
        $admin = $this->saveAdmin($admin, $permission);

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $admin->getId(),
        ));

        return $view;
    }

    /**
     * @param ShopAdmin $admin
     * @param array     $permission
     *
     * @return ShopAdmin
     */
    private function saveAdmin(
        $admin,
        $permission
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        // set platform permissions
        $this->saveAdminPermissions(
            $admin,
            $permission,
            $em
        );

        // set sales company
        $salesCompany = $this->getUser()->getMyAdmin()->getSalesCompany();
        $admin->setSalesCompany($salesCompany);

        $admin->setCreationDate($now);
        $admin->setModificationDate($now);

        // save admin
        $em->persist($admin);
        $em->flush();

        return $admin;
    }

    /**
     * @param $admin
     * @param $permissions
     * @param $em
     */
    private function saveAdminPermissions(
        $admin,
        $permissions,
        $em
    ) {
        $now = new \DateTime('now');
        if (is_null($permissions) || empty($permissions)) {
            return;
        }

        foreach ($permissions as $permissionId) {
            // get permission
            $myPermission = $this->getRepo('Shop\ShopAdminPermission')
                ->find($permissionId['id']);
            if (is_null($myPermission)
                || $myPermission->getTypeId() != $admin->getType()->getId()
            ) {
                // if permission's type is different
                // don't add the permission
                continue;
            }

            // save permission map
            $permissionMap = new ShopAdminPermissionMap();
            $permissionMap->setAdmin($admin);
            $permissionMap->setPermission($myPermission);
            $permissionMap->setCreationDate($now);
            $permissionMap->setOpLevel($permissionId['op_level']);

            if (isset($permissionId['shop_id'])) {
                $permissionMap->setShopId($permissionId['shop_id']);
            }
            $em->persist($permissionMap);
        }
    }

    /**
     * @param ShopAdmin $admin
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
        $adminExist = $this->getRepo('Shop\ShopAdmin')->findOneByUsername($admin->getUsername());
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
            $type = $this->getRepo('Shop\ShopAdminType')->find($admin->getTypeId());
            if (is_null($type)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_ADMIN_TYPE_CODE,
                    self::ERROR_ADMIN_TYPE_MESSAGE);
            }
        }
    }

    /**
     * @param $opLevel
     * @param $permissions
     * @param $shopId
     */
    private function checkAdminAdminsPermission(
        $opLevel,
        $permissions,
        $shopId = null
    ) {
        $this->throwAccessDeniedIfShopAdminNotAllowed(
            $this->getAdminId(),
            ShopAdminType::KEY_PLATFORM,
            $permissions,
            $opLevel,
            $shopId
        );
    }
}
