<?php

namespace Sandbox\SalesApiBundle\Controller\Space;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AdminSpaceSalesCompanyController.
 */
class AdminSpaceSalesCompanyController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/company/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesCompanyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($id);
        $this->throwNotFoundIfNull($salesCompany, self::NOT_FOUND_MESSAGE);

        return new View(array(
            'name' => $salesCompany->getName(),
        ));
    }
}
