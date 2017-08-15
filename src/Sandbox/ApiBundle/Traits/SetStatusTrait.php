<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;

/**
 * Consume Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait SetStatusTrait
{
    use ConsumeTrait;
    use FinanceTrait;

    /**
     * @param ProductOrder $order
     */
    protected function setProductOrderStatusCompleted(
        $order
    ) {
        $order->setStatus(ProductOrder::STATUS_COMPLETED);
        $order->setModificationDate(new \DateTime('now'));

        $type = $order->getType();

        if ($type == ProductOrder::PREORDER_TYPE) {
            $parameter = Parameter::KEY_BEAN_PRODUCT_ORDER_PREORDER;

            $this->generateLongRentServiceFee(
                $order->getOrderNumber(),
                $order->getProduct()->getRoom()->getBuilding()->getCompanyId(),
                $order->getDiscountPrice(),
                $order->getPayChannel(),
                FinanceLongRentServiceBill::TYPE_BILL_POUNDAGE
            );
        } else {
            $parameter = Parameter::KEY_BEAN_PRODUCT_ORDER;
        }

        if ($order->getCustomerId()) {
            $customer = $this->getContainer()->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($order->getCustomerId());

            $userId = $customer ? $customer->getUserId() : null;
        } else {
            $userId = $order->getUserId();
        }

        if ($userId) {
            //update user bean
            $this->getContainer()->get('sandbox_api.bean')->postBeanChange(
                $userId,
                $order->getDiscountPrice(),
                $order->getOrderNumber(),
                $parameter
            );

            //update invitee bean
            $user = $this->getContainer()->get('doctrine')
                ->getRepository('SandboxApiBundle:User\User')
                ->find($userId);

            if ($user->getInviterId()) {
                $this->getContainer()->get('sandbox_api.bean')->postBeanChange(
                    $user->getInviterId(),
                    $order->getDiscountPrice(),
                    $order->getOrderNumber(),
                    Parameter::KEY_BEAN_INVITEE_PRODUCT_ORDER
                );
            }
        }
    }

    protected function setProductOrderInvoice(
        $order
    ) {
        $price = $order->getDiscountPrice();
        $userId = $order->getUserId();

        // set invoice amount
        $amount = $this->postConsumeBalance(
            $userId,
            $price,
            $order->getOrderNumber()
        );

        if (!is_null($amount)) {
            $order->setInvoiced(true);
        }
    }

    /**
     * @param EventOrder $order
     */
    protected function setEventOrderStatusCompleted(
        $order
    ) {
        $order->setStatus(EventOrder::STATUS_COMPLETED);
        $order->setModificationDate(new \DateTime('now'));

        $price = $order->getPrice();

        if ((EventOrder::CHANNEL_ACCOUNT != $order->getPayChannel())
            && $price > 0
        ) {
            // set invoice amount
            $this->postConsumeBalance(
                $order->getUserId(),
                $price,
                $order->getOrderNumber()
            );
        }
    }
}
