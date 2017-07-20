<?php

namespace Sandbox\SalesApiBundle\Controller\Customer;

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
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/enterprise_customers/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putEnterpriseCustomerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $em = $this->getDoctrine()->getManager();

        $enterpriseCustomer = $em->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
            ->find($id);
        $this->throwNotFoundIfNull($enterpriseCustomer, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(new EnterpriseCustomerType(), $enterpriseCustomer, array('method' => 'PUT'));
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $this->handleContacts($enterpriseCustomer);

        return new View();
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="pageIndex",
     *     default=1,
     *     nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="pageLimit",
     *     default=20,
     *     nullable=true
     * )
     *
     * @Route("/enterprise_customers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEnterpriseCustomersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $enterpriseCustomers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
            ->findBy(array(
                'companyId' => $salesCompanyId,
            ));

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $enterpriseCustomers,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
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
        $enterpriseCustomerId = $enterpriseCustomer->getId();

        // remove old data
        $oldContacts = $em->getRepository('SandboxApiBundle:User\EnterpriseCustomerContacts')
            ->findBy(array(
                'enterpriseCustomerId' => $enterpriseCustomerId,
            ));
        foreach ($oldContacts as $item) {
            $em->remove($item);
        }
        $em->flush();

        // add new data
        foreach ($contacts as $contact) {
            $contactObject  = new EnterpriseCustomerContacts();

            $form = $this->createForm(new EnterpriseCustomerContactType(), $contactObject);
            $form->submit($contact);

            if (!$form->isValid()) {
                continue;
            }

            $contactObject->setEnterpriseCustomerId($enterpriseCustomerId);
            $em->persist($contactObject);
        }

        $em->flush();

        return ;
    }
}