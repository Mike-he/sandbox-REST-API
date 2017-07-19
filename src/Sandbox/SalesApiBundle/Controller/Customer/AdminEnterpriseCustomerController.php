<?php

namespace Sandbox\SalesApiBundle\Controller\Customer;

use FOS\RestBundle\Request\ParamFetcherInterface;
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

class AdminEnterpriseCustomerController extends SalesRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/enterprise_customers")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postEnterpriseCustomerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $enterpriseCustomer = new EnterpriseCustomer();

        $form = $this->createForm(new EnterpriseCustomerType(), $enterpriseCustomer);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $enterpriseCustomer->setCompanyId($salesCompanyId);

        $em->persist($enterpriseCustomer);
        $em->flush();

        $this->handleContacts($enterpriseCustomer);

        return new View(array(
            'id' => $enterpriseCustomer->getId(),
        ));
    }

    /**
     * @param EnterpriseCustomer $enterpriseCustomer
     */
    private function handleContacts(
        $enterpriseCustomer
    ) {
        $contacts = $enterpriseCustomer->getContacts();

        if (is_null($contacts) || empty($contacts)) {
            return ;
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($contacts as $contact) {
            $contactObject  = new EnterpriseCustomerContacts();

            $form = $this->createForm(new EnterpriseCustomerContactType(), $contactObject);
            $form->submit($contact);

            if (!$form->isValid()) {
                continue;
            }

            $contactObject->setEnterpriseCustomerId($enterpriseCustomer->getId());
            $em->persist($contactObject);
        }

        $em->flush();

        return ;
    }
}