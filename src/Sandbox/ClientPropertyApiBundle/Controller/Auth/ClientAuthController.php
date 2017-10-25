<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Auth;

use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ClientAuthController extends AuthController
{
    /**
     * Token auth.
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
     * @Route("/auth/company")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getSalesCompaniesAction(
        Request $request
    ) {
        $myAdminId = $this->getAdminId();

        $companies = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->findCompanyByAdmin(
                $myAdminId,
                AdminPosition::PLATFORM_SALES
            );

        foreach ($companies as &$company) {
            $attachment = $this->getDoctrine()
               ->getRepository('SandboxApiBundle:Room\RoomBuildingAttachment')
               ->findAttachmentByCompany($company['id']);

            $company['content'] = $attachment ? $attachment[0]['content'] : '';
        }

        $view = new View();
        $view->setData($companies);

        return $view;
    }
}
