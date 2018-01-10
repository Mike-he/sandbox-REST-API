<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionUserBinding;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * SalesAdmin controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminShopAdminsController extends SandboxRestController
{
    const POSITION_ADMIN = '超级管理员';
    const POSITION_COFFEE_ADMIN = '超级管理员';

    /**
     * Create admin.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Method({"POST"})
     * @Route("/admins/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminsAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkShopAdminPermission(AdminPermission::OP_LEVEL_EDIT);

        $userId = $request->get('user_id');
        $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $this->createPosition(
            $user,
            $company
        );

        return new View();
    }

    /**
     * Update Admin.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/admins/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putAdminAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkShopAdminPermission(AdminPermission::OP_LEVEL_EDIT);

        $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $userId = $request->get('user_id');
        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        return $this->handleAdminPut(
            $user,
            $company
        );
    }

    /**
     * @param $user
     * @param $company
     *
     * @return View
     */
    private function handleAdminPut(
        $user,
        $company
    ) {
        $em = $this->getDoctrine()->getManager();

        $adminPosition = $em->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(
                array(
                    'salesCompany' => $company,
                    'name' => self::POSITION_COFFEE_ADMIN,
                    'platform' => AdminPermission::PERMISSION_PLATFORM_SHOP,
                    'isSuperAdmin' => true,
                )
            );

        $adminPositionUser = $em->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findOneBy(array('position' => $adminPosition));

        $adminPositionUser->setUser($user);
        $em->flush();

        return new View();
    }

    /**
     * @param $user
     * @param $company
     *
     * @return AdminPosition
     */
    private function createPosition(
        $user,
        $company
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime('now');

        $position = $em->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->findOneBy(
                array(
                    'salesCompany' => $company,
                    'name' => self::POSITION_COFFEE_ADMIN,
                    'platform' => AdminPermission::PERMISSION_PLATFORM_SHOP,
                    'isSuperAdmin' => true,
                    'isHidden' => false,
                )
            );

        if ($position) {
            $adminPositionUser = $em->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->findOneBy(array('position' => $position));

            if ($adminPositionUser) {
                $adminPositionUser->setUser($user);
            } else {
                $adminPositionUser = new AdminPositionUserBinding();
                $adminPositionUser->setUser($user);
                $adminPositionUser->setPosition($position);
                $adminPositionUser->setCreationDate($now);
                $em->persist($adminPositionUser);
            }
        } else {
            $icon = $em->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')->find(1);

            $position = new AdminPosition();
            $position->setName(self::POSITION_COFFEE_ADMIN);
            $position->setPlatform(AdminPermission::PERMISSION_PLATFORM_SHOP);
            $position->setIsSuperAdmin(true);
            $position->setIcon($icon);
            $position->setSalesCompany($company);
            $position->setCreationDate($now);
            $position->setModificationDate($now);
            $em->persist($position);

            $adminPositionUser = new AdminPositionUserBinding();
            $adminPositionUser->setUser($user);
            $adminPositionUser->setPosition($position);
            $adminPositionUser->setCreationDate($now);
            $em->persist($adminPositionUser);
        }

        $em->flush();

        return $position;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    protected function checkShopAdminPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES],
            ],
            $opLevel
        );
    }
}
