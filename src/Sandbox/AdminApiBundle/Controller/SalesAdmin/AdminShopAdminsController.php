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
use JMS\Serializer\SerializationContext;

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
class AdminShopAdminsController extends SandboxRestController
{
    const POSITION_ADMIN = 'admin';
    const POSITION_COFFEE_ADMIN = 'coffee_admin';

    /**
     * List definite id of admin.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"GET"})
     * @Route("/admins/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminAction(
        Request $request,
        $id
    ) {
        // check user permission

//        $salesAdmin = $this->getRepo('SalesAdmin\SalesAdmin')->find($id);
//        $this->throwNotFoundIfNull($salesAdmin, self::NOT_FOUND_MESSAGE);

//        // get admin
//        $admins = $this->getRepo('Shop\ShopAdmin')->findOneByCompanyId($salesAdmin->getCompanyId());

//        // set view
//        $view = new View($admins);
//        $view->setSerializationContext(
//            SerializationContext::create()->setGroups(array('admin'))
//        );

//        return $view;
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

        $userId = $request->get('user_id');
        $company = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $this->createPosition(
            $user,
            $company,
            self::POSITION_COFFEE_ADMIN
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
     * @param $name
     *
     * @return AdminPosition
     */
    private function createPosition(
        $user,
        $company,
        $name
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime('now');

        $icon = $em->getRepository('SandboxApiBundle:Admin\AdminPositionIcons')->find(1);

        $position = new AdminPosition();
        $position->setName($name);
        $position->setParentPositionId(0);
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

        $em->flush();

        return $position;
    }
}
