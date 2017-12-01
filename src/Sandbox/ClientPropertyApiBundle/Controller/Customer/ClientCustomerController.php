<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Customer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
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
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Constants\EventOrderExport;

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
            ->searchSalesCustomers(
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
     * @param $id
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
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $customer = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);
        $userId = $customer->getUserId();

        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_ORDER,
            )
        );

        $productOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countCustomerAllProductOrders($id, $myBuildingIds);

        $eventOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->countCustomerAllEventOrders($id,$salesCompanyId);

        $membershipOrdersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->countCustomerAllMembershipOrders($userId);

        $billsCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countCustomerAllLeaseBills($id,$myBuildingIds);

        $leaseCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countCustomerAllLeases($id,$myBuildingIds);

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

        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_ORDER,
            )
        );

        $productOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findCustomerProductsOrder(
                $id,
                $myBuildingIds,
                $limit,
                $offset
            );
           // ->findBy(array('customerId'=>$id),array('creationDate'=>'DESC'), $limit, $offset);

        $receivableTypes = [
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        $orderLists = [];
        foreach ($productOrders as $order) {
            $orderLists[] = $this->handleOrderData(
                $id,
                $order,
                $receivableTypes
            );
        }
        return new View($orderLists);
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

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $eventOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->findCustomerEventOrder(
                $id,
                $salesCompanyId,
                $limit,
                $offset
            );

        $orderLists = [];
        foreach ($eventOrders as $order) {
            $orderLists[] = $this->handleEventOrderData($id,$order);
        }

        return new View($orderLists);
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
            ->findBy(array('user'=>$userId),array('creationDate'=>'DESC'), $limit, $offset);

        $orderLists = [];
        foreach ($MembershipOrders as $order) {
            $orderLists[] = $this->handleMembershipOrderData($id,$order);
        }

        return new View($orderLists);
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

        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_ORDER,
            )
        );

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findCustomerLeaseBill(
                $id,
                $myBuildingIds
            );

        $receivableTypes = [
            'sales_wx' => '微信',
            'wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'cash' => '现金',
            'sales_others' => '其他',
            'others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
            'remit' => '线下汇款'
        ];

        $bills = $this->handleBillData($bills, $limit, $offset, $receivableTypes);

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

        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_ORDER,
            )
        );

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findCustomerLease(
                $id,
                $myBuildingIds,
                $limit,
                $offset
            );

        $ids = array();
        foreach($leases as $lease){
            $ids[] = $lease->getId();
        }

        $leases = $this->handleLeaseData($ids);

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

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="query",
     *     array=false,
     *     default=null,
     *     strict=true,
     *     nullable=true
     * )
     *
     * @Route("/open/customer_or_user")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getQueryCustomerOrUserAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $search = $paramFetcher->get('query');

        if (is_null($search)) {
            return new View([]);
        }

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        if (!filter_var($search, FILTER_VALIDATE_EMAIL)) {
            $customers = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->searchSalesCustomers(
                    $salesCompanyId,
                    $search
                );

            if (!empty($customers)) {
                return new View($customers);
            }
        }

        $users = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->searchSalesUsers($search);

        if (!empty($users)) {
            return new View($users);
        }

        return new View([]);
    }

    /**
     * @param $billIds
     * @param $limit
     * @param $offset
     * @param $receivableTypes
     * @return array
     */
    private function handleBillData(
        $billIds,
        $limit,
        $offset,
        $receivableTypes
    ) {
        $ids = array();
        for ($i = $offset; $i < $offset + $limit; ++$i) {
            if (isset($billIds[$i])) {
                $ids[] = $billIds[$i];
            }
        }

        $result = [];
        foreach ($ids as $id) {
            $bill = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->find($id);

            /** @var Lease $lease */
            $lease = $bill->getLease();
            /** @var Product $product */
            $product = $lease->getProduct();
            $room = $product->getRoom();
            $building = $room->getBuilding();

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($bill->getCustomerId());

            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($room->getId(), 1);

            $roomAttachment = [];
            if (!empty($attachment)) {
                $roomAttachment['content'] = $attachment[0]['content'];
                $roomAttachment['preview'] = $attachment[0]['preview'];
            }

            $payChannel = '';
            if ($bill->getPayChannel()) {
                if (LeaseBill::CHANNEL_SALES_OFFLINE == $bill->getPayChannel()) {
                    $receivable = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                        ->findOneBy([
                            'orderNumber' => $bill->getSerialNumber(),
                        ]);
                    if ($receivable) {
                        $payChannel = $receivableTypes[$receivable->getPayChannel()];
                    }
                } else {
                    $payChannel = '创合钱包支付';
                }
            }

            $status = $this->get('translator')
                ->trans(LeaseConstants::TRANS_LEASE_BILL_STATUS.$bill->getStatus());

            $result[] = [
                'id' => $id,
                'serial_number' => $bill->getSerialNumber(),
                'send_date' => $bill->getSendDate(),
                'name' => $bill->getName(),
                'room_name' => $room->getName(),
                'building_name' => $building->getName(),
                'start_date' => $bill->getStartDate(),
                'end_date' => $bill->getEndDate(),
                'amount' => (float) $bill->getAmount(),
                'revised_amount' => (float) $bill->getRevisedAmount(),
                'status' => $status,
                'pay_channel' => $payChannel,
                'customer' => array(
                    'id' => $bill->getCustomerId(),
                    'name' => $customer ? $customer->getName() : '',
                    'avatar' => $customer ? $customer->getAvatar() : '',
                ),
                'room_attachment' => $roomAttachment,
            ];
        }

        return $result;
    }

    /**
     * @param ProductOrder $order
     * @param $receivableTypes
     * @param $id
     *
     * @return array
     */
    private function handleOrderData(
        $id,
        $order,
        $receivableTypes
    ) {
        $room = $order->getProduct()->getRoom();
        $building = $room->getBuilding();

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);

        $avatar = '';
        if ($customer->getAvatar()) {
            $avatar = $customer->getAvatar();
        } elseif ($customer->getUserId()) {
            $avatar = $this->getParameter('image_url').'/person/'.$customer->getUserId().'/avatar_small.jpg';
        }

        $customerData = [
            'id' => $order->getCustomerId(),
            'name' => $customer->getName(),
            'avatar' => $avatar,
        ];

        $attachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findAttachmentsByRoom($room->getId(), 1);

        $roomAttachment = [];
        if (!empty($attachment)) {
            $roomAttachment['content'] = $attachment[0]['content'];
            $roomAttachment['preview'] = $attachment[0]['preview'];
        }

        $payChannel = '';
        if ($order->getPayChannel()) {
            if (ProductOrder::CHANNEL_SALES_OFFLINE == $order->getPayChannel()) {
                $receivable = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
                    ->findOneBy([
                        'orderNumber' => $order->getOrderNumber(),
                    ]);
                if ($receivable) {
                    $payChannel = $receivableTypes[$receivable->getPayChannel()];
                }
            } else {
                $payChannel = '创合钱包支付';
            }
        }

        $roomType = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$room->getType());
        $orderType = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_TYPE.$order->getType());
        $status = $this->get('translator')->trans(ProductOrderExport::TRANS_PRODUCT_ORDER_STATUS.$order->getStatus());

        $result = array(
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'creation_date' => $order->getCreationDate(),
            'status' => $status,
            'start_date' => $order->getStartDate(),
            'end_date' => $order->getEndDate(),
            'room_attachment' => $roomAttachment,
            'room_type_description' => $roomType,
            'room_type' => $room->getType(),
            'room_name' => $room->getName(),
            'building_name' => $building->getName(),
            'price' => (float) $order->getPrice(),
            'discount_price' => (float) $order->getDiscountPrice(),
            'order_type' => $orderType,
            'pay_channel' => $payChannel,
            'base_price' => $order->getBasePrice(),
            'unit_price' => $order->getUnitPrice(),
            'customer' => $customerData,
        );

        return $result;
    }

    /**
     * @param $id
     * @param $order
     *
     * @return array
     */
    private function handleEventOrderData(
        $id,
        $order
    ) {
        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($id);

        /** @var Event $event */
        $event = $order->getEvent();

        $eventAttachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventAttachment')
            ->findOneBy(array('eventId' => $event->getId()));

        $status = $this->get('translator')->trans(EventOrderExport::TRANS_EVENT_ORDER_STATUS.$order->getStatus());

        $result = array(
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'creation_date' => $order->getCreationDate(),
            'status' => $status,
            'event_name' => $event->getname(),
            'event_start_date' => $event->getEventStartDate(),
            'event_end_date' => $event->getEventEndDate(),
            'event_status' => $event->getStatus(),
            'address' => $event->getAddress(),
            'price' => (float) $order->getPrice(),
            'pay_channel' => $order->getPayChannel() ? '创合钱包支付' : '',
            'customer' => array(
                'id' => $order->getCustomerId(),
                'name' => $customer ? $customer->getName() : '',
                'avatar' => $customer ? $customer->getAvatar() : '',
            ),
            'attachment' => $eventAttachment ? $eventAttachment->getContent() : '',
        );

        return $result;
    }

    /**
     * @param MembershipOrder $order
     *
     * @return array
     */
    private function handleMembershipOrderData(
        $id,
        $order
    ) {
        $card = $order->getCard();
        $userId = $order->getUser();

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'id' => $id,
                'companyId' => $card->getCompanyId(),
            ));

        $result = array(
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'payment_date' => $order->getPaymentDate(),
            'name' => $card->getName(),
            'specification' => $order->getSpecification(),
            'start_date' => $order->getStartDate(),
            'end_date' => $order->getEndDate(),
            'price' => $order->getPrice(),
            'pay_channel' => $order->getPayChannel() ? '创合钱包支付' : '',
            'status' => '已付款',
            'background' => $card->getBackground(),
            'customer' => array(
                'id' => $customer ? $customer->getId() : '',
                'name' => $customer ? $customer->getName() : '',
                'avatar' => $customer ? $customer->getAvatar() : '',
            ),
        );

        return $result;
    }

    /**
     * @param Lease $leaseIds
     *
     * @return array
     */
    private function handleLeaseData(
        $leaseIds
    ) {
        $result = [];
        foreach ($leaseIds as $id) {
            $lease = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\Lease')
                ->find($id);

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($lease->getLesseeCustomer());

            $status = $this->get('translator')
                ->trans(LeaseConstants::TRANS_LEASE_STATUS.$lease->getStatus());

            /** @var Product $product */
            $product = $lease->getProduct();
            $room = $product->getRoom();
            $building = $room->getBuilding();

            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($room->getId(), 1);

            $roomType = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$room->getType());

            $paidBillsCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    null,
                    LeaseBill::STATUS_PAID
                );

            $totalBillsCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease
                );

            $paidBillsAmount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->sumBillsFees(
                    $lease,
                    LeaseBill::STATUS_PAID
                );

            $result[] = [
                'id' => $id,
                'serial_number' => $lease->getSerialNumber(),
                'creation_date' => $lease->getCreationDate(),
                'status' => $status,
                'start_date' => $lease->getStartDate(),
                'end_date' => $lease->getEndDate(),
                'room_type' => $roomType,
                'room_name' => $room->getName(),
                'room_attachment' => $attachment,
                'building_name' => $building->getName(),
                'total_rent' => (float) $lease->getTotalRent(),
                'paid_amount' => (float) $paidBillsAmount,
                'paid_bills_count' => $paidBillsCount,
                'total_bills_count' => $totalBillsCount,
                'customer' => array(
                    'id' => $lease->getLesseeCustomer(),
                    'name' => $customer ? $customer->getName() : '',
                    'avatar' => $customer ? $customer->getAvatar() : '',
                ),
            ];
        }

        return $result;
    }
}
