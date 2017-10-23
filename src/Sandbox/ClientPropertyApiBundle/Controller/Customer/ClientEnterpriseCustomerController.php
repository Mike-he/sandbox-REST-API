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

class ClientEnterpriseCustomerController extends SalesRestController
{
    /**
     * @param Request   $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
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
        $keyword = 'name';
        $keywordSearch = $paramFetcher->get('search');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $enterpriseCustomers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
            ->getClientSalesEnterpriseCustomers(
                $salesCompanyId,
                $keyword,
                $keywordSearch
            );

       $count = count($enterpriseCustomers);
        return new View([
            "item" => $enterpriseCustomers,
            'total_count' => $count,

        ]);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     * @Route("/enterprise_customer/{id}")
     * @Method({"GET"})
     * @return View
     */
    public function getEnterpriseCustomerAction
    (
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ){
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $enterpriseCustomer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
            ->findOneBy(array(
                'id' => $id,
                'companyId' => $salesCompanyId,
            ));
        $this->throwNotFoundIfNull($enterpriseCustomer, self::NOT_FOUND_MESSAGE);

        $contacts = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\EnterpriseCustomerContacts')
            ->findBy(array(
                'enterpriseCustomerId' => $enterpriseCustomer->getId(),
            ));

        foreach($contacts as $contact){
            $contactCustomer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($contact->getCustomerId());

            $contact->setUserCustomer($contactCustomer);
        }

        $enterpriseCustomer->setContacts($contacts);

        return new View($enterpriseCustomer);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     * @Route("/enterprise_customer/{id}/lease_and_bill/count")
     * @return View
     */
    public function getEnterPriseCusomerLeasesAndBillsCountAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ){
        $leasesCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countEnterpriseCustomerLease($id);

        $billsCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countEnterprisseCustomerLeaseBill($id);

        $view = new View();
        $view->setData(
            array(
                'leasesCount'=>$leasesCount,
                'billsCount'=>$billsCount
            )
        );

        return $view;
    }

    /**
     * @param Request               $request
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

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/enterprise_customer/{id}/leases")
     * @Method({"GET"})
     * @return View
     */
    public function getEnterPriseCustomerleasesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ){
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findBy(array('lesseeEnterprise'=>$id),array('creationDate'=>'DESC'), $limit, $offset);

        return new View($lease);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/enterprise_customer/{id}/bills")
     * @Method({"GET"})
     * @return View
     */
    public function getEnterPriseCustomerleaseBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ){
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->getClientEnterpriseCustomerLeaseBills(
                $id,
                $limit,
                $offset
            );

        return new View($bills);
    }

    /**
     * @param EnterpriseCustomer $enterpriseCustomer
     */
    private function handleContacts(
        $enterpriseCustomer
    ) {
        $contacts = $enterpriseCustomer->getContacts();

        if (is_null($contacts)) {
            return;
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
            $contactObject = new EnterpriseCustomerContacts();

            $form = $this->createForm(new EnterpriseCustomerContactType(), $contactObject);
            $form->submit($contact);

            if (!$form->isValid()) {
                continue;
            }

            $contactObject->setEnterpriseCustomerId($enterpriseCustomerId);
            $em->persist($contactObject);
        }

        $em->flush();

        return;
    }
}