<?php

namespace Sandbox\ClientApiBundle\Controller\SalesAdmin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;

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

        $profile = $this->getDoctrine()->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfiles')
            ->findOneBy([
                'salesCompanyId'=>$id
            ]);

        return new View([
            'id' => $salesCompany->getId(),
            'name' => $salesCompany->getName(),
            'cover' => $profile ? $profile->getCover() : '',
            'description' => $salesCompany->getDescription()
        ]);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="ids",
     *     array=true,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("/sales/companies/enterprises")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesCompanyEnterprises(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('ids');

        $salesCompanies = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findBy(array(
                'id' => $ids,
            ));

        $result = array();
        foreach ($salesCompanies as $salesCompany) {
            $profile = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfiles')
                ->findOneBy(array('salesCompanyId' => $salesCompany->getId()));

            $service = $this->getDoctrine()->getRepository('SandboxApiBundle:Service\Service')
                ->findOneBy(
                    array(
                        'salesCompanyId' => $salesCompany->getId(),
                        'visible' => true,
                    ),
                    array('id' => 'desc')
                );

            $type = $service ? $service->getType() : null;

            $latestServiceType = '';
            if ($type) {
                $serviceType = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Service\ServiceTypes')
                    ->findOneBy(array('key' => $type));

                $latestServiceType = $serviceType ? $serviceType->getName() : '';
            }

            $result[] = array(
                'id' => $salesCompany->getId(),
                'name' => $salesCompany->getName(),
                'cover' => $profile ? $profile->getCover() : '',
                'latest_service_name' => $service ? $service->getName() : '',
                'latest_service_type' => $latestServiceType,
            );
        }

        return new View($result);
    }
}
