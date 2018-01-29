<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\AdminApiBundle\Data\Admin\AdminCheckPermission;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Form\Admin\AdminPostCheckPermissionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin Permissions Controller.
 *
 * @category Sandbox
 *
 * @author  Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminPermissionsController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/permissions/groups_map")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminPermissionsGroupMapAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        $groups = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findBy(array(
                'platform' => $platform,
            ));

        $response = array();
        foreach ($groups as $group) {
            $item = array(
                'group' => array(
                    'id' => $group->getId(),
                    'group_name' => $group->getGroupName(),
                ),
                'permissions' => array(),
            );

            $maps = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
                ->findBy(array(
                    'group' => $group,
                ));

            foreach ($maps as $map) {
                $permission = $map->getPermission();

                $permissionsArray = array(
                    'id' => $permission->getId(),
                    'name' => $permission->getName(),
                    'max_op_level' => $permission->getMaxOpLevel(),
                    'op_level_select' => $permission->getOpLevelSelect(),
                    'level' => $permission->getLevel(),
                );

                array_push($item['permissions'], $permissionsArray);
            }

            array_push($response, $item);
        }

        return new View($response);
    }

    /**
     * List all admin permissions.
     *
     * @param Request $request the request object
     *
     * @Annotations\QueryParam(
     *    name="salesCompanyId",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
     * )
     *
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     default=null,
     *     strict=true,
     *     description="platform key"
     * )
     *
     * @Method({"GET"})
     * @Route("/permissions")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminPermissionsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $platform = $adminPlatform['platform'];

        // get all admin permissions
        $query = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->getAdminPermissions(
                $platform,
                $salesCompanyId
            );

        $view = new View($query);
        $view->setSerializationContext(SerializationContext::create()
            ->setGroups(array('main')));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/check_my_permissions")
     * @Method({"POST"})
     *
     * @return View
     */
    public function checkMyAdminPermissions(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminCheckPermission = new AdminCheckPermission();
        $form = $this->createForm(new AdminPostCheckPermissionType(), $adminCheckPermission);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            $adminCheckPermission->getPermissions(),
            $adminCheckPermission->getOpLevel(),
            $adminCheckPermission->getPlatform(),
            $adminCheckPermission->getSalesCompanyId()
        );
    }
}
