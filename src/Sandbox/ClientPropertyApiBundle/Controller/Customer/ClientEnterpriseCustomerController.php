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
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Constants\ProductOrderExport;

class ClientEnterpriseCustomerController extends SalesRestController
{
    /**
     * @param Request   $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="name",
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
        $search = $paramFetcher->get('name');

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $enterpriseCustomers = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
            ->getClientSalesEnterpriseCustomers(
                $salesCompanyId,
                $search
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

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findBy(array('lesseeEnterprise'=>$id),array('creationDate'=>'DESC'), $limit, $offset);

        $ids = array();
        foreach($leases as $lease){
            $ids[] = $lease->getId();
        }

        $leases = $this->handleLeaseData($ids);

        return new View($leases);
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
                $id
            );

        $ids = array();
        foreach($bills as $bill){
            $ids[] = $bill->getId();
        }

        $receivableTypes = [
            'wx' => '微信',
            'sales_wx' => '微信',
            'sales_alipay' => '支付宝支付',
            'sales_cash' => '现金',
            'sales_others' => '其他',
            'sales_pos' => 'POS机',
            'sales_remit' => '线下汇款',
        ];

        $bills = $this->handleBillData($ids, $limit, $offset, $receivableTypes);

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
                ->find($lease->getLesseeCustomer());

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
                    'id' => $lease->getLesseeCustomer(),
                    'name' => $customer ? $customer->getName() : '',
                    'avatar' => $customer ? $customer->getAvatar() : '',
                ),
                'room_attachment' => $roomAttachment,
            ];
        }

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