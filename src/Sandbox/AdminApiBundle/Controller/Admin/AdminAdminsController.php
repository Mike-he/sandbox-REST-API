<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Form\Admin\AdminPostType;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;

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
     * @Method({"GET"})
     * @Route("/admins")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminsAction(
        Request $request
    ) {
        // check user permission
        $this->throwAccessDeniedIfAdminNotAllowed($this->getAdminId(), AdminType::KEY_SUPER);

        // get all admins
        $admins = $this->getRepo('Admin\Admin')->findAll();

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
        $this->throwAccessDeniedIfAdminNotAllowed($this->getAdminId(), AdminType::KEY_SUPER);

        // bind admin data
        $admin = new Admin();
        $form = $this->createForm(new AdminPostType(), $admin);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleAdminCreate($admin);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
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
        $this->throwAccessDeniedIfAdminNotAllowed($myAdminId, AdminType::KEY_SUPER);

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
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        // set permissions
        $permissionIds = $admin->getPermissionIds();
        if (!is_null($permissionIds) && !empty($permissionIds)) {
            foreach ($permissionIds as $permissionId) {
                // get permission
                $myPermission = $this->getRepo('Admin\AdminPermission')->find($permissionId);
                if (is_null($myPermission)
                    || $myPermission->getTypeId() != $admin->getTypeId()
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
                $em->persist($permissionMap);
            }
        }

        // set dates
        $admin->setCreationDate($now);
        $admin->setModificationDate($now);

        // save admin
        $em->persist($admin);
        $em->flush();

        return $admin;
    }
}
