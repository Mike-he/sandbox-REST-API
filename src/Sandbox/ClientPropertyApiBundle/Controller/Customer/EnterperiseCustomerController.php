<?php
namespace Sandbox\ClientPropertyApiBundle\Controller\Customer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\User\EnterpriseCustomer;
use Sandbox\ApiBundle\Entity\User\EnterpriseCustomerContacts;
use Sandbox\ApiBundle\Form\User\EnterpriseCustomerContactType;
use Sandbox\ApiBundle\Form\User\EnterpriseCustomerType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;

class EnterpriseCustomerController extends SalesRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @Route("/enterprise/customers")
     * @Method("{GET}")
     * @return View
     */
    public function getEnterpriseCustomersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $keyword = 'name';
        $keywordSearch = $paramFetcher->get('search');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $enterpriseCustomers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
            ->searchSalesEnterpriseCustomers(
                $salesCompanyId,
                $keyword,
                $keywordSearch
            );

        foreach ($enterpriseCustomers as $enterpriseCustomer) {
            $contacts = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\EnterpriseCustomerContacts')
                ->findBy(array(
                    'enterpriseCustomerId' => $enterpriseCustomer->getId(),
                ));

            $enterpriseCustomer->setContacts($contacts);
        }

        $paginator = new Paginator();
        $response = $paginator->paginate(
            $enterpriseCustomers,
            $pageIndex,
            $pageLimit
        );

        return new View($response);
    }


}