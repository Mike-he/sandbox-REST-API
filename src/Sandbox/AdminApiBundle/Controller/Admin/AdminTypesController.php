<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin Types Controller.
 *
 * @category Sandbox
 *
 * @author  Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminTypesController extends SandboxRestController
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
        $this->throwAccessDeniedIfAdminNotAllowed($this->getAdminId(), AdminType::KEY_SUPER);

        // get all admin types
        $query = $this->getRepo('Admin\AdminType')->findAll();

        $view = new View($query);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['auth']));

        return $view;
    }
}
