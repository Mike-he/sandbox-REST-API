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
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Shop Admin Permissions Controller.
 *
 * @category Sandbox
 *
 * @author  Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminPermissionsController extends ShopRestController
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
        $this->throwAccessDeniedIfShopAdminNotAllowed(
            $this->getAdminId(),
            ShopAdminType::KEY_PLATFORM,
            array(
                ShopAdminPermission::KEY_PLATFORM_ADMIN,
            ),
            ShopAdminPermissionMap::OP_LEVEL_VIEW
        );

        if ($typeId == null) {
            // get all admin permissions
            $query = $this->getRepo('Shop\ShopAdminPermission')->findAll();
        } else {
            // get admin permissions by typeId
            $query = $this->getRepo('Shop\ShopAdminPermission')->findBy(array(
                'typeId' => $typeId,
            ));
        }

        $view = new View($query);
        $view->setSerializationContext(SerializationContext::create()
            ->setGroups(array('main')));

        return $view;
    }
}
