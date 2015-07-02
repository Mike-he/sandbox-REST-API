<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\Admin\AdminLoginController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Form\Admin\AdminType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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
class AdminAdminsController extends AdminLoginController
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

        $adminUser = $this->getUser();

        $admin = new Admin();

        // bind client data
        $form = $this->createForm(new AdminType(), $admin);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $type = $this->getRepo('Admin\AdminType')->find($admin->getTypeId());
        if (is_null($type)) {
            $this->throwNotFoundIfNull($type, self::NOT_FOUND_MESSAGE);
        }

        $admin->setType($type);

        $now = new \DateTime('now');

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

        $admin->setCreationDate($now);
        $admin->setModificationDate($now);

        var_dump($admin);

        $em = $this->getDoctrine()->getManager();
        $em->persist($admin);
        $em->flush();

        $view = new View();
        $view->setData(array(
           'id' => $admin->getId(),
        ));

        return $view;
    }
}
