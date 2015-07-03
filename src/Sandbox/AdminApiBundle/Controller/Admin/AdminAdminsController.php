<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Form\Admin\AdminType as AdminForm;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @return string
     *
     * @throws \Exception
     */
    public function postAdminsAction(
        Request $request
    ) {
        // TODO a common function to check user permission

        $user = $this->getUser();
        $myAdmin = $this->getRepo('Admin\Admin')->find($user->getAdminId());

        // only super admin is allowed to create admin account
        $type = $this->getRepo('Admin\AdminType')->findOneByKey(AdminType::KEY_SUPER);
        if ($type->getId() != $myAdmin->getTypeId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // bind admin data
        $admin = new Admin();
        $form = $this->createForm(new AdminForm(), $admin);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleAdminCreate($admin);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param Admin $admin
     *
     * @return View
     */
    private function handleAdminCreate(
        $admin
    ) {
        // check username
        if (is_null($admin->getUsername())) {
            return $this->customErrorView(
                400,
                self::ERROR_USERNAME_INVALID_CODE,
                self::ERROR_USERNAME_INVALID_MESSAGE);
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
            $admin->setType($type);
        }

        // save admin to db
        $admin = $this->saveAdmin($admin);

        // set view
        $view = new View();
        $view->setData(array(
            'id' => $admin->getId(),
        ));

        return $view;
    }

    /**
     * @param Admin $admin
     *
     * @return Admin
     */
    private function saveAdmin(
        $admin
    ) {
        $now = new \DateTime('now');

        // set permissions
        $permissionIds = array();
        foreach ($admin->getPermissions() as $permission) {
            $permissionId = $permission['id'];

            $myPermission = $this->getRepo('Admin\AdminPermission')->find($permissionId);
            if (is_null($myPermission)) {
                continue;
            }

            $permissionMap = new AdminPermissionMap();
            $permissionMap->setAdmin($admin);
            $permissionMap->setPermission($myPermission);
            $permissionMap->setCreationDate($now);

            array_push($permissionIds, $permissionMap);
        }
        $admin->setPermissionIds($permissionIds);

        // set dates
        $admin->setCreationDate($now);
        $admin->setModificationDate($now);

        // save admin
        $em = $this->getDoctrine()->getManager();
        $em->persist($admin);
        $em->flush();

        return $admin;
    }
}
