<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermission;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminPermissionMap;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Sales Admin Permissions Controller.
 *
 * @category Sandbox
 *
 * @author  Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminPermissionsController extends SalesRestController
{
    /**
     * List all admin permissions.
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
     * @Annotations\QueryParam(
     *    name="type_id",
     *    array=false,
     *    default= null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
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
        $typeId = $paramFetcher->get('type_id');

        // check user permission
        $this->throwAccessDeniedIfSalesAdminNotAllowed(
            $this->getAdminId(),
            SalesAdminType::KEY_PLATFORM,
            array(
                SalesAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            SalesAdminPermissionMap::OP_LEVEL_VIEW
        );

        if ($typeId == null) {
            // get all admin permissions
            $query = $this->getRepo('SalesAdmin\SalesAdminPermission')->findAll();
        } else {
            // get admin permissions by typeId
            $query = $this->getRepo('SalesAdmin\SalesAdminPermission')->findBy(array(
                'typeId' => $typeId,
            ));
        }

        $view = new View($query);
        $view->setSerializationContext(SerializationContext::create()
            ->setGroups(array('main')));

        return $view;
    }
}
