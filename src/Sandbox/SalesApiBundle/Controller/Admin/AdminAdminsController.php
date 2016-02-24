<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesAdminPutType;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesPlatformAdminPostType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
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
 * SalesAdmin controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAdminsController extends SalesRestController
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
     *    description="sales admin types"
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
    public function getSalesAdminsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            SalesAdminPermissionMap::OP_LEVEL_VIEW
        );

        $typeKey = $paramFetcher->get('type');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        // get all admins id and username
        $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey($typeKey);
        $companyId = $this->getUser()->getMyAdmin()->getCompanyId();
        $query = $this->getRepo('SalesAdmin\SalesAdmin')->findBy(array(
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
     * @param Request $request  the request object
     * @param int     $admin_id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"GET"})
     * @Route("/admins/{admin_id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminAction(
        Request $request,
        $admin_id
    ) {
        // check user permission
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            SalesAdminPermissionMap::OP_LEVEL_VIEW
        );

        // get all admins
        $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey(AdminType::KEY_PLATFORM);
        $companyId = $this->getUser()->getMyAdmin()->getCompanyId();
        $admins = $this->getRepo('SalesAdmin\SalesAdmin')->findOneBy(array(
            'id' => $admin_id,
            'companyId' => $companyId,
            'typeId' => $type->getId(),
        ));

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
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            SalesAdminPermissionMap::OP_LEVEL_EDIT
        );

        // bind admin data
        $admin = new SalesAdmin();
        $form = $this->createForm(new SalesPlatformAdminPostType(), $admin);
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
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            SalesAdminPermissionMap::OP_LEVEL_EDIT
        );

        $admin = $this->getRepo('SalesAdmin\SalesAdmin')->find($id);
        $passwordOld = $admin->getPassword();

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
        $permission = $form['permission']->getData();

        return $this->handleAdminPatch(
            $id,
            $admin,
            $type_key,
            $permission
        );
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
        $myAdminId = $this->getAdminId();

        // check user permission
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            SalesAdminPermissionMap::OP_LEVEL_EDIT
        );

        // get admin
        $admin = $this->getRepo('SalesAdmin\SalesAdmin')->find($id);

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
     * @param SalesAdmin              $admin
     * @param SalesAdmin              $id
     * @param SalesAdminType          $type_key
     * @param SalesAdminPermissionMap $permissionPuts
     *
     * @return View
     */
    private function handleAdminPatch(
        $id,
        $admin,
        $type_key,
        $permissionPuts
    ) {
        $em = $this->getDoctrine()->getManager();
        if (!is_null($type_key)) {
            $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey($type_key);
            $admin->setTypeId($type->getId());
        }
        $em->persist($admin);

        if (!is_null($permissionPuts)) {
            //judge the value of permissions
            $permissions = $this->getRepo('SalesAdmin\SalesAdminPermissionMap')
                ->findBy(array('adminId' => $id));

            $permissionSame = array();
            foreach ($permissions as $pOld) {
                foreach ($permissionPuts as $pNew) {
                    if ($pOld->getPermissionId() == $pNew['id'] && $pOld->getBuildingId() == $pNew['building_id']) {
                        $permissionSame[] = $pNew;
                        if ($pOld->getOpLevel() != $pNew['op_level']) {
                            $permissionMap = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminPermissionMap')
                                ->findOneBy(
                                    array(
                                        'adminId' => $id,
                                        'permissionId' => $pOld->getPermissionId(),
                                        'buildingId' => $pOld->getBuildingId(),
                                    )
                                );
                            $permissionMap->setOpLevel($pNew['op_level']);
                        }
                    }
                }
            }
            //remove the useless permissions
            foreach ($permissions as $permissionOld) {
                $num = 0;
                foreach ($permissionSame as $pSame) {
                    if ($permissionOld->getPermissionId() == $pSame['id']
                        && $permissionOld->getBuildingId() == $pSame['building_id']) {
                        $num = 1;
                    }
                }
                if ($num == 0) {
                    $pRemove = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminPermissionMap')
                        ->findOneBy(
                            array(
                                'adminId' => $id,
                                'permissionId' => $permissionOld->getPermissionId(),
                            )
                        );
                    $em->remove($pRemove);
                }
            }

            //set the new permissions
            $now = new \DateTime('now');
            foreach ($permissionPuts as $pNew) {
                $num = 0;
                foreach ($permissionSame as $pSame) {
                    if ($pNew['id'] == $pSame['id'] && $pNew['building_id'] == $pSame['building_id']) {
                        $num = 1;
                    }
                }
                if ($num == 0) {
                    // get permission
                    $myPermission = $this->getRepo('SalesAdmin\SalesAdminPermission')->find($pNew['id']);
                    if (is_null($myPermission)
                        || $myPermission->getTypeId() != $admin->getTypeId()
                    ) {
                        // if permission's type is different
                        // don't add the permission
                        continue;
                    }
                    // save permission map
                    $permissionMap = new SalesAdminPermissionMap();
                    $permissionMap->setAdminId($id);
                    $permissionMap->setPermissionId($pNew['id']);
                    $permissionMap->setCreationDate($now);
                    $permissionMap->setAdmin($admin);
                    $permissionMap->setPermission($myPermission);
                    $permissionMap->setOpLevel($pNew['op_level']);

                    if (isset($pNew['building_id']) && !is_null($pNew['building_id'])) {
                        $permissionMap->setBuildingId($pNew['building_id']);
                    }
                    $em->persist($permissionMap);
                }
            }
        }

        //save data
        $em->flush();

        return new View();
    }

    /**
     * @param SalesAdmin     $admin
     * @param SalesAdminType $type_key
     * @param array          $permission
     *
     * @return View
     */
    private function handleAdminCreate(
        $admin,
        $type_key,
        $permission
    ) {
        $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey($type_key);
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
     * @param SalesAdmin $admin
     * @param array      $permission
     *
     * @return SalesAdmin
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
        if (!is_null($permissions) && !empty($permissions)) {
            foreach ($permissions as $permissionId) {
                // get permission
                $myPermission = $this->getRepo('SalesAdmin\SalesAdminPermission')
                    ->find($permissionId['id']);
                if (is_null($myPermission)
                    || $myPermission->getTypeId() != $admin->getType()->getId()
                ) {
                    // if permission's type is different
                    // don't add the permission
                    continue;
                }

                // save permission map
                $permissionMap = new SalesAdminPermissionMap();
                $permissionMap->setAdmin($admin);
                $permissionMap->setPermission($myPermission);
                $permissionMap->setCreationDate($now);
                $permissionMap->setOpLevel($permissionId['op_level']);

                if (isset($permissionId['building_id'])) {
                    $permissionMap->setBuildingId($permissionId['building_id']);
                }
                $em->persist($permissionMap);
            }
        }
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
}
