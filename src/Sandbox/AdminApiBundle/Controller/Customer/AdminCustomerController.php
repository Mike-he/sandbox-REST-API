<?php

namespace Sandbox\AdminApiBundle\Controller\Customer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;

class AdminCustomerController extends AdminRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by id"
     * )
     *
     *
     * @Route("/open/customers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOpenUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $ids = $paramFetcher->get('id');

        $customers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->searchCustomers(
                null,
                $ids
            );

        foreach ($customers as &$customer) {
            if ($customer['user_id']) {
                $user = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\User')
                    ->find($customer['user_id']);

                if ($user) {
                    $profile = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:User\UserProfile')
                        ->findOneBy(array('userId' => $customer['user_id']));

                    $customer['user'] = array(
                        'id' => $user->getId(),
                        'phone' => $user->getPhone(),
                        'email' => $user->getEmail(),
                        'name' => $profile ? $profile->getName() : '',
                    );
                }
            }
        }

        return new View($customers);
    }

    /**
     * @param Request $request
     *
     *
     * @Route("/customers/enterprise/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEnterpriseAction(
        Request $request,
        $id
    ) {
        $enterprise = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
            ->find($id);

        return new View($enterprise);
    }
}
