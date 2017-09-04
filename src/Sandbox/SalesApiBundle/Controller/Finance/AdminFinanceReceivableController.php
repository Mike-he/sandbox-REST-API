<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Finance\FinanceReceivables;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Finance Receivable Controller.
 */
class AdminFinanceReceivableController extends SalesRestController
{
    use LeaseTrait;

    /**
     * @param Request $request the request object
     *
     * @Route("/finance/receivable")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function receivableAction(
        Request $request
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT],
                ['key' => AdminPermission::KEY_SALES_BUILDING_CASHIER],
            ],
            AdminPermission::OP_LEVEL_EDIT
        );

        $payloads = json_decode($request->getContent(), true);

        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime();

        $logMessage = '确认收款';
        foreach ($payloads as $payload) {
            $orderNumber = $payload['order_number'];

            $firstLetter = substr($orderNumber, 0, 1);
            switch ($firstLetter) {
                case ProductOrder::LETTER_HEAD:
                    $order = $em->getRepository('SandboxApiBundle:Order\ProductOrder')
                        ->findOneBy(array(
                            'orderNumber' => $orderNumber,
                            'status' => ProductOrder::STATUS_UNPAID,
                        ));

                    if (!$order) {
                        continue;
                    }
                    $order->setStatus(ProductOrder::STATUS_PAID);
                    $order->setPaymentDate($now);
                    $order->setModificationDate($now);
                    $order->setPayChannel(ProductOrder::CHANNEL_SALES_OFFLINE);

                    $amount = $order->getDiscountPrice();
                    break;
                case LeaseBill::LEASE_BILL_LETTER_HEAD:
                    $bill = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
                        ->findOneBy(array(
                            'serialNumber' => $orderNumber,
                            'status' => LeaseBill::STATUS_UNPAID,
                        ));

                    if (!$bill) {
                        continue;
                    }

                    $customerId = $bill->getLease()->getLesseeCustomer();

                    $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($customerId);
                    if (!$customer) {
                        continue;
                    }

                    $bill->setPayChannel(LeaseBill::CHANNEL_SALES_OFFLINE);
                    $bill->setPaymentDate($now);
                    $bill->setStatus(LeaseBill::STATUS_PAID);
                    $bill->setCustomerId($customerId);
                    $bill->setDrawee($customer->getUserId());

                    $invoiced = $this->checkBillShouldInvoiced($bill->getLease());
                    if (!$invoiced) {
                        $bill->setInvoiced(true);
                    }

                    // Status Log
                    $this->get('sandbox_api.admin_status_log')->addLog(
                        $this->getAdminId(),
                        LeaseBill::STATUS_PAID,
                        $logMessage,
                        AdminStatusLog::OBJECT_LEASE_BILL,
                        $bill->getId()
                    );

                    $amount = $bill->getRevisedAmount();
                    break;
                default:
                    continue;
            }

            $receivable = new FinanceReceivables();
            $receivable->setOrderNumber($orderNumber);
            $receivable->setPayChannel($payload['pay_channel']);
            $receivable->setAmount($amount);
            $receivable->setTransactionNumber($payload['transaction_number']);
            $receivable->setRemark($payload['remark']);
            $receivable->setReceiver($this->getAdminId());
            $em->persist($receivable);
        }
        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/finance/receivable")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="order_number",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="order number"
     * )
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getReceivableAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $orderNumber = $paramFetcher->get('order_number');

        $receivable = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceReceivables')
            ->findBy(array('orderNumber' => $orderNumber));

        return new View($receivable);
    }
}
