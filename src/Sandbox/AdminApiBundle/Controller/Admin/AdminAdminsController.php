<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Form\Admin\AdminPostType;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Form\Admin\AdminPutType;
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
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ADMIN,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        // get all admins id and username
        $query = $this->getRepo('Admin\Admin')->findAll();

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
            AdminPermission::KEY_PLATFORM_ADMIN,
            AdminPermissionMap::OP_LEVEL_VIEW
        );

        // get all admins
        $admins = $this->getRepo('Admin\Admin')->findOneBy(array('id' => $admin_id));

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
            AdminPermission::KEY_PLATFORM_ADMIN,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        // bind admin data
        $admin = new Admin();
        $form = $this->createForm(new AdminPostType(), $admin);
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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ADMIN,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        $admin = $this->getRepo('Admin\Admin')->find($id);

        // bind data
        $adminJson = $this->container->get('serializer')->serialize($admin, 'json');
        $patch = new Patch($adminJson, $request->getContent());
        $adminJson = $patch->apply();

        $form = $this->createForm(new AdminPutType(), $admin);
        $form->submit(json_decode($adminJson, true));

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
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ADMIN,
            AdminPermissionMap::OP_LEVEL_EDIT
        );

        // get admin
        $admin = $this->getRepo('Admin\Admin')->find($id);

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
     * @param Admin              $admin
     * @param Admin              $id
     * @param AdminType          $typeKey
     * @param AdminPermissionMap $permissionInComing
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
            $type = $this->getRepo('Admin\AdminType')->findOneByKey($typeKey);
            $admin->setTypeId($type->getId());
        }
        $em->persist($admin);

        if (is_null($permissionInComing) || empty($permissionInComing)) {
            // save data
            $em->flush();

            return new View();
        }

        $permissionInComingIds = array();

        // check incoming permissions in db
        foreach ($permissionInComing as $item) {
            // get permission
            $permission = $this->getRepo('Admin\AdminPermission')->find($item['id']);
            if (is_null($permission)) {
                continue;
            }

            $permissionId = $permission->getId();

            // generate incoming permission id array
            array_push($permissionInComingIds, $permissionId);

            // get from db
            $permissionDb = $this->getRepo('Admin\AdminPermissionMap')->findOneBy(array(
                'adminId' => $id,
                'permissionId' => $permissionId,
            ));

            // not in db
            if (is_null($permissionDb)) {
                // save permission map
                $permissionMap = new AdminPermissionMap();
                $permissionMap->setCreationDate($now);
                $permissionMap->setAdmin($admin);
                $permissionMap->setPermission($permission);
                $permissionMap->setOpLevel($item['op_level']);

                $em->persist($permissionMap);

                continue;
            }

            // opLevel change
            if ($item['op_level'] != $permissionDb->getOpLevel()) {
                $permissionDb->setOpLevel($item['op_level']);
            }
        }

        // remove permissions from db
        $permissionDbAll = $this->getRepo('Admin\AdminPermissionMap')->findByAdminId($id);
        foreach ($permissionDbAll as $item) {
            if (!in_array($item->getPermissionId(), $permissionInComingIds)) {
                $em->remove($item);
            }
        }

        // save data
        $em->flush();

        return new View();
    }

    /**
     * @param Admin              $admin
     * @param AdminType          $type_key
     * @param AdminPermissionMap $permission
     *
     * @return View
     */
    private function handleAdminCreate(
        $admin,
        $type_key,
        $permission
    ) {
        $type = $this->getRepo('Admin\AdminType')->findOneByKey($type_key);
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
     * @param Admin              $admin
     * @param AdminPermissionMap $permission
     *
     * @return Admin
     */
    private function saveAdmin(
        $admin,
        $permission
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        // set dates
        $admin->setCreationDate($now);
        $admin->setModificationDate($now);

        // save admin
        $em->persist($admin);

        if (is_null($permission) || empty($permission)) {
            $em->flush();

            return $admin;
        }

        // set permissions
        foreach ($permission as $permissionId) {
            // get permission
            $myPermission = $this->getRepo('Admin\AdminPermission')
                            ->find($permissionId['id']);
            if (is_null($myPermission)
                || $myPermission->getTypeId() != $admin->getType()->getId()
            ) {
                // if permission's type is different
                // don't add the permission
                continue;
            }

            // save permission map
            $permissionMap = new AdminPermissionMap();
            $permissionMap->setAdmin($admin);
            $permissionMap->setPermission($myPermission);
            $permissionMap->setCreationDate($now);
            $permissionMap->setOpLevel($permissionId['op_level']);
            $em->persist($permissionMap);
        }

        $em->flush();

        return $admin;
    }

    /**
     * @param $admin
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
        $adminExist = $this->getRepo('Admin\Admin')->findOneByUsername($admin->getUsername());
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
