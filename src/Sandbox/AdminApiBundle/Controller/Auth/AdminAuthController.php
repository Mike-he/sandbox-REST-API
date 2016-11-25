<?php

namespace Sandbox\AdminApiBundle\Controller\Auth;

use Sandbox\AdminApiBundle\Controller\Traits\HandleAdminLoginDataTrait;
use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin Auth controller.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.f@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAuthController extends AuthController
{
    use HandleAdminLoginDataTrait;

    /**
     * Token auth.
     *
     * @param Request $request the request object
     *
     * @Route("/me")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminAuthMeAction(
        Request $request
    ) {
        $adminId = $this->getAdminId();
        $platform = $request->query->get('platform');
        $salesCompanyId = $request->query->get('sales_company_id');

        // response for openfire
        if (is_null($platform)) {
            return new View(array(
                'id' => $this->getUser()->getUserId(),
                'client_id' => $this->getUser()->getClientId(),
            ));
        }

        // response my permissions
        if ($platform !== 'official') {
            if (is_null($salesCompanyId)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        }

        $condition = $this->hasSuperAdminPosition(
            $adminId,
            $platform,
            $salesCompanyId
        );

        if ($condition) {
            $permissions = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findSuperAdminPermissionsByPlatform(
                    $platform,
                    $salesCompanyId
                );
        } else {
            $permissions = $this->getMyAdminPermissions(
                $adminId,
                $platform,
                $salesCompanyId
            );
        }

        // add permission group
        $permissions = $this->generatePermissionsGroup($permissions);

        $admin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->find($adminId);

        // response
        return new View(
            array(
                'permissions' => $this->remove_duplicate($permissions),
                'admin' => [
                    'id' => $admin->getId(),
                    'name' => $admin->getName(),
                    'phone' => $admin->getPhone(),
                    'is_super_admin' => $condition,
                ],
            )
        );
    }

    /**
     * @param array $permissions
     *
     * @return mixed
     */
    private function generatePermissionsGroup(
        $permissions
    ) {
        $responsePermissions = array();

        foreach ($permissions as $permission) {
            if (isset($permission['permission_parent_id']) && !is_null($permission['permission_parent_id'])) {
                $parentPermission = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPermission')
                    ->find($permission['permission_parent_id']);

                $permission['group'] = $this->transferGroupKey($parentPermission->getKey());
            } else {
                $permission['group'] = $this->transferGroupKey($permission['key']);
            }

            array_push($responsePermissions, $permission);
        }

        return $responsePermissions;
    }

    /**
     * @param $permissionKey
     *
     * @return mixed
     */
    private function transferGroupKey(
        $permissionKey
    ) {
        $permissionKeyArray = explode('.', $permissionKey);
        $group = array_slice($permissionKeyArray, -1, 1);

        return $group[0];
    }

    /**
     * GET positions of platform when admin refresh login page.
     *
     * @Route("/platform")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminAuthPlatformAction()
    {
        $myAdminId = $this->getAdminId();

        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findPositionByAdmin($myAdminId);

        // response
        $view = new View();

        return $view->setData(
            array(
                'platform' => $this->handlePositionData($positions),
            )
        );
    }
}
