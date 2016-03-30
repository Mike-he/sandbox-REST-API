<?php

namespace Sandbox\AdminShopApiBundle\Controller\Admin;

use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\AdminShopApiBundle\Controller\ShopRestController;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermission;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminPermissionMap;
use Sandbox\ApiBundle\Entity\Shop\ShopAdminType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Shop Admin Types Controller.
 *
 * @category Sandbox
 *
 * @author  Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminTypesController extends ShopRestController
{
    /**
     * List all admin types.
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
     * @Route("/types")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminTypesAction(
        Request $request
    ) {
        // check user permission
        $this->throwAccessDeniedIfShopAdminNotAllowed(
            $this->getAdminId(),
            ShopAdminType::KEY_PLATFORM,
            array(
                ShopAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            ShopAdminPermissionMap::OP_LEVEL_VIEW
        );

        // get all admin types
        $query = $this->getRepo('Shop\ShopAdminType')->findAll();

        $view = new View($query);
        $view->setSerializationContext(SerializationContext::create()
            ->setGroups(array('main')));

        return $view;
    }
}
