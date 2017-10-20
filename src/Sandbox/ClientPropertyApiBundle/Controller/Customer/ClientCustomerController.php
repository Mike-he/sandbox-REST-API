<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Customer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\User\UserCustomer;
use Sandbox\ApiBundle\Entity\User\UserGroupHasUser;
use Sandbox\ApiBundle\Form\User\UserCustomerPatchType;
use Sandbox\ApiBundle\Form\User\UserCustomerType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientCustomerController extends SalesRestController
{
    const ERROR_CUSTOMER_EXIST_CODE = 400001;
    const ERROR_CUSTOMER_EXIST_MESSAGE = 'Customer exist';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="search",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("/customers")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCustomersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $search = $paramFetcher->get('search');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $customers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->getClientSalesAdminCustomers(
                $salesCompanyId,
                $search
            );

        $count = count($customers);

        return new View([
            'items' => $customers,
            'total_count' => $count,
        ]);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/customer/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCustomerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'id' => $id,
                'companyId' => $salesCompanyId,
            ));
        $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);

        $this->generateCustomer($customer);

        $userId = $customer->getUserId();
        if ($userId) {
            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->find($userId);

            $customer->setCardNo($user->getCardNo());
        }

        return new View($customer);
    }


    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @Route("/customers/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchCustomerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);
        $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);

        $customerJson = $this->container->get('serializer')->serialize($customer, 'json');
        $patch = new Patch($customerJson, $request->getContent());
        $customerJson = $patch->apply();

        $form = $this->createForm(new UserCustomerPatchType(), $customer);
        $form->submit(json_decode($customerJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @Route("/customer")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postCustomerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $customer = new UserCustomer();

        $form = $this->createForm(new UserCustomerType(), $customer);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $phoneCode = $customer->getPhoneCode();
        $phone = $customer->getPhone();

        $customerOrigin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
                'companyId' => $salesCompanyId,
            ));

        if ($customerOrigin) {
            return $this->customErrorView(
                400,
                self::ERROR_CUSTOMER_EXIST_CODE,
                self::ERROR_CUSTOMER_EXIST_MESSAGE
            );
        }

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
            ));

        if ($user) {
            $customer->setUserId($user->getId());
        }

        $customer->setCompanyId($salesCompanyId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($customer);
        $em->flush();

        return new View(array('id' => $customer->getId()), 201);
    }

    /**
     * @param $customer
     */
    private function generateCustomer(
        $customer
    ) {
        /** @var UserCustomer $customer */
        $groupBinds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findBy(array(
                'customerId' => $customer->getId(),
            ));

        $groups = [];
        foreach ($groupBinds as $bind) {
            array_push($groups, $bind->getGroupId());

            $groups = array_unique($groups);
        }

        $customerGroupArray = [];
        foreach ($groups as $groupId) {
            /** @var UserGroupHasUser $bind */
            $group = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserGroup')
                ->find($groupId);

            array_push($customerGroupArray, [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'type' => $group->getType(),
            ]);
        }

        $customer->setGroups($customerGroupArray);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     * @Route("/customer/{id}/orders/number")
     * @Method({"GET"})
     * @return View
     */
    public function getCustomerAllOrdersNumAction
    (
        Request $request,
        ParamFetcherInterface  $paramFetcher,
        $id
    ){
        $customer = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);
        $userId = $customer->getUserId();

        $productOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countCustomerAllProductOrders($userId);

        $eventOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->countCustomerAllEventOrders($userId);

        $membershipOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->countCustomerAllMembershipOrders($userId);

        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array('userId'=>$userId));

        $billsCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countCustomerAllLeaseBills($id);

        $leaseCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countCustomerAllLeases($id);

        $view = new View();
        $view->setData(
            array(
                'productOrdersCount'=>$productOrdersCount,
                'eventOrdersCount'=>$eventOrdersCount,
                'membershipOrdersCount'=>$membershipOrdersCount,
                'billCount' =>$billsCount,
                'leaseCount' => $leaseCount
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @param $id
     * @Route("/customer/{id}/product_orders")
     * @Method({"GET"})
     * @return View
     */
    public function getCustomerProductOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $customer = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);
        $userId = $customer->getUserId();

        $productOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findBy(array('userId'=>$userId),array('creationDate'=>'DESC'), $limit, $offset);

        return new View($productOrders);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @param $id
     * @Route("/customer/{id}/event_orders")
     * @Method({"GET"})
     * @return View
     */
    public function getCustomerEventOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $customer = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);
        $userId = $customer->getUserId();

        $eventOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->findBy(array('userId'=>$userId),array('creationDate'=>'DESC'), $limit, $offset);

        return new View($eventOrders);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @param $id
     * @Route("/customer/{id}/membership_orders")
     * @Method({"GET"})
     * @return View
     */
    public function getCustomerMembershipOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $customer = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);
        $userId = $customer->getUserId();

        $MembershipOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->findBy(array('userId'=>$userId),array('creationDate'=>'DESC'), $limit, $offset);

        return new View($MembershipOrders);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @param $id
     * @Route("/customer/{id}/lease_bills")
     * @return View
     */
    public function getCustomerLeaseBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBy(array('customerId'=>$id),array('sendDate'=>'DESC'), $limit, $offset);

        return new View($bills);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @param $id
     * @Route("/customer/{id}/leases")
     * @return View
     */
    public function getCustomerLeasesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findBy(array('customerId'=>$id),array('creationDate'=>'DESC'), $limit, $offset);

        return new View($leases);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/customers/{id}/phone")
     * @Method({"POST"})
     *
     * @return View
     */
    public function switchCustomersPhoneAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy([
                'id' => $id,
                'isAutoCreated' => false,
            ]);
        $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);

        $customerId = $customer->getId();

        $data = json_decode($request->getContent(), true);
        $phoneCode = $data['phone_code'];
        $phone = $data['phone'];

        if (!$phoneCode || !$phone) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $em = $this->getDoctrine()->getManager();

        $customerOrigin = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
                'companyId' => $salesCompanyId,
            ));
        if ($customerOrigin) {
            $customerNewId = $customerOrigin->getId();
        } else {
            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy(array(
                    'phoneCode' => $phoneCode,
                    'phone' => $phone,
                ));

            $userId = $user ? $user->getId() : null;
            $customerId = $customer->getId();
            $customerNew = new UserCustomer();
            $customerNew->setPhoneCode($phoneCode);
            $customerNew->setPhone($phone);
            $customerNew->setUserId($userId);
            $customerNew->setCompanyId($salesCompanyId);
            $customerNew->setName($customer->getName());
            $customerNew->setSex($customer->getSex());
            $customerNew->setAvatar($customer->getAvatar());
            $customerNew->setBirthday($customer->getBirthday());
            $customerNew->setEmail($customer->getEmail());
            $customerNew->setNationality($customer->getNationality());
            $customerNew->setIdType($customer->getIdType());
            $customerNew->setIdNumber($customer->getIdNumber());
            $customerNew->setCompanyName($customer->getCompanyName());
            $customerNew->setPosition($customer->getPosition());
            $em->persist($customerNew);
            $em->flush();

            $customerNewId = $customerNew->getId();
        }

        // update bills & leases & backend push orders
        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBy(['customerId' => $customerId]);

        foreach ($bills as $bill) {
            $bill->setCustomerId($customerNewId);
        }

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findBy(['lesseeCustomer' => $customerId]);
        foreach ($leases as $lease) {
            $lease->setLesseeCustomer($customerNewId);
        }

        $pushOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findBy([
                'customerId' => $customerId,
                'type' => [ProductOrder::PREORDER_TYPE, ProductOrder::OFFICIAL_PREORDER_TYPE],
            ]);
        foreach ($pushOrders as $order) {
            $order->setCustomerId($customerNewId);
        }

        $em->flush();

        return new View(array(
            'id' => $customerNewId,
        ));
    }
}
