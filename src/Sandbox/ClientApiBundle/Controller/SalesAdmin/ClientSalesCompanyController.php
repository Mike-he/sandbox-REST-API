<?php

namespace Sandbox\ClientApiBundle\Controller\SalesAdmin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ClientSalesCompanyController extends SandboxRestController
{
    /**
     * @param $id
     *
     * @Route("/sales/companies/{id}/description")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesCompanyDescription(
        $id
    ) {
        $salesCompany = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($id);

        $this->throwNotFoundIfNull($salesCompany, self::NOT_FOUND_MESSAGE);

        return new View([
            'id' => $salesCompany->getId(),
            'name' => $salesCompany->getName(),
            'description' => $salesCompany->getDescription()
        ]);
    }
}