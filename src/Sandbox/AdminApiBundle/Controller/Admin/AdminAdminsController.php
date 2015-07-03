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

        return $this->handleAdminCreate($request);
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    private function handleAdminCreate(
        $request
    ) {
        // handle admin create data
        $admin = $this->handleAdminCreateData($request);

        // save admin to db
        $admin = $this->saveAdmin($admin);

        $view = new View();
        $view->setData(array(
            'id' => $admin->getId(),
        ));

        return $view;
    }

    /**
     * @param Request $request
     *
     * @return Admin
     */
    private function handleAdminCreateData(
        $request
    ) {
        $admin = new Admin();

        // bind admin data
        $form = $this->createForm(new AdminForm(), $admin);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // TODO return custom error


        return $admin;
    }

    /**
     * @param Admin $admin
     *
     * @return Admin
     */
    private function saveAdmin(
        $admin
    ) {
        // set type
        $type = $this->getRepo('Admin\AdminType')->find($admin->getTypeId());
        if (is_null($type)) {
            $this->throwNotFoundIfNull($type, self::NOT_FOUND_MESSAGE);
        }
        $admin->setType($type);

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
