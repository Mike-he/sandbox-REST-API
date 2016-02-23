<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
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
     *    default="super",
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
        $typeKey = $paramFetcher->get('type');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES_ADMIN,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        // get all admins id and username
        $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey($typeKey);
        $query = $this->getRepo('SalesAdmin\SalesAdmin')->findByTypeId($type->getId());

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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES_ADMIN,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        // get all admins
        $admins = $this->getRepo('SalesAdmin\SalesAdmin')->findOneBy(array('id' => $admin_id));

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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES_ADMIN,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        // bind admin data
        $admin = new SalesAdmin();
        $form = $this->createForm(new SalesAdminPostType(), $admin);
        $form->handleRequest($request);

        $type_key = $form['type_key']->getData();
        $permission = $form['permission']->getData();
        $company = $form['company']->getData();

        if (is_null($company)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if ($form->isValid()) {
            return $this->handleAdminCreate(
                $admin,
                $type_key,
                $permission,
                $company
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
            AdminPermission::KEY_PLATFORM_SALES_ADMIN,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        $admin = $this->getRepo('SalesAdmin\SalesAdmin')->find($id);

        // bind data
        $adminJson = $this->container->get('serializer')->serialize($admin, 'json');
        $patch = new Patch($adminJson, $request->getContent());
        $adminJson = $patch->apply();

        $form = $this->createForm(new SalesAdminPutType(), $admin);
        $form->submit(json_decode($adminJson, true));

        $type_key = $form['type_key']->getData();
        $permission = $form['permission']->getData();
        $company = $form['company']->getData();

        return $this->handleAdminPatch(
            $id,
            $admin,
            $type_key,
            $permission,
            $company
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_SALES_ADMIN,
            AdminPermissionMap::OP_LEVEL_EDIT
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
     * @param SalesCompany            $company
     *
     * @return View
     */
    private function handleAdminPatch(
        $id,
        $admin,
        $type_key,
        $permissionPuts,
        $company
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

            $permissionSameId = array();
            foreach ($permissions as $pOld) {
                foreach ($permissionPuts as $pNew) {
                    if ($pOld->getPermissionId() == $pNew['id']) {
                        $permissionSameId[] = $pNew['id'];
                        if ($pOld->getOpLevel() != $pNew['op_level']) {
                            $permissionMap = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminPermissionMap')
                                ->findOneBy(
                                    array(
                                        'adminId' => $id,
                                        'permissionId' => $pOld->getPermissionId(),
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
                foreach ($permissionSameId as $pSameId) {
                    if ($permissionOld->getPermissionId() == $pSameId) {
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
                foreach ($permissionSameId as $pSameId) {
                    if ($pNew['id'] == $pSameId) {
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
                    $em->persist($permissionMap);
                }
            }
        }

        // set sales company
        $salesCompany = $this->getRepo('SalesAdmin\SalesCompany')->find($admin->getCompanyId());
        $form = $this->createForm(new SalesCompanyPostType(), $salesCompany);
        $form->submit($company);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        //save data
        $em->flush();

        return new View();
    }

    /**
     * @param SalesAdmin              $admin
     * @param SalesAdminType          $type_key
     * @param SalesAdminPermissionMap $permission
     * @param SalesCompany            $company
     *
     * @return View
     */
    private function handleAdminCreate(
        $admin,
        $type_key,
        $permission,
        $company
    ) {
        $type = $this->getRepo('SalesAdmin\SalesAdminType')->findOneByKey($type_key);
        $admin->setType($type);
        $admin->setTypeId($type->getId());

        $checkAdminValid = $this->checkAdminValid($admin);
        if (!is_null($checkAdminValid)) {
            return $checkAdminValid;
        }

        // save admin to db
        $admin = $this->saveAdmin($admin, $permission, $company);

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $admin->getId(),
        ));

        return $view;
    }

    /**
     * @param SalesAdmin              $admin
     * @param SalesAdminPermissionMap $permission
     * @param SalesCompany            $company
     *
     * @return SalesAdmin
     */
    private function saveAdmin(
        $admin,
        $permission,
        $company
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        // set permissions
        if (!is_null($permission) && !empty($permission)) {
            foreach ($permission as $permissionId) {
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
                $em->persist($permissionMap);
            }
        }

        // set sales company
        $salesCompany = new SalesCompany();
        $form = $this->createForm(new SalesCompanyPostType(), $salesCompany);
        $form->submit($company);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $admin->setSalesCompany($salesCompany);

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
            $type = $this->getRepo('Admin\AdminType')->find($admin->getTypeId());
            if (is_null($type)) {
                return $this->customErrorView(
                    400,
                    self::ERROR_ADMIN_TYPE_CODE,
                    self::ERROR_ADMIN_TYPE_MESSAGE);
            }
        }
    }
}
