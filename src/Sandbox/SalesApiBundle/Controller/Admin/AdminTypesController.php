<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sales Admin Types Controller.
 *
 * @category Sandbox
 *
 * @author  Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminTypesController extends SalesRestController
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
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            SalesAdminPermissionMap::OP_LEVEL_VIEW
        );

        // get all admin types
        $query = $this->getRepo('SalesAdmin\SalesAdminType')->findAll();

        $view = new View($query);
        $view->setSerializationContext(SerializationContext::create()
            ->setGroups(array('main')));

        return $view;
    }
}
