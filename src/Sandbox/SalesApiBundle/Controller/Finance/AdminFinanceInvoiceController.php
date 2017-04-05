<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

/**
 * Admin Finance Long Rent Service Bill Controller.
 */
class AdminFinanceInvoiceController extends SalesRestController
{
    /**
     * Get Finance Invoice Category.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/invoice/categories")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFinanceInvoiceCategoryAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $companyInvioces = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
            ->findBy(array(
                'company' => $salesCompanyId,
                'drawer' => SalesCompanyServiceInfos::DRAWER_SALES,
            ));

        $category = array();
        foreach ($companyInvioces as $companyInvioce) {
            $category[] = array(
                'category' => $companyInvioce->getInvoicingSubjects(),
            );
        }

        return new View($category);
    }
}
