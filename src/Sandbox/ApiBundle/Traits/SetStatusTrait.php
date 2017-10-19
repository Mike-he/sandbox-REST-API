<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Finance\FinanceLongRentServiceBill;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Entity\User\UserBeanFlow;

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
        $payChannel = $order->getPayChannel();

        if ($payChannel != ProductOrder::CHANNEL_SALES_OFFLINE) {
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
                $amount = $this->getContainer()->get('sandbox_api.bean')->postBeanChange(
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
                        Parameter::KEY_BEAN_INVITEE_PRODUCT_ORDER,
                        UserBeanFlow::TYPE_ADD,
                        $amount
                    );
                }
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

    /**
     * @param Event $event
     */
    protected function setEventStatus(
        $event
    ) {
        $now = new \DateTime();

        $registrationStartDate = $event->getRegistrationStartDate();
        $registrationEndDate = $event->getRegistrationEndDate();

        $eventStartDate = $event->getEventStartDate();
        $eventEndDate = $event->getEventEndDate();

        $status = $event->getStatus();

        if ($now >= $registrationStartDate &&
            $now <= $registrationEndDate &&
            $status != Event::STATUS_REGISTERING)
        {
            $event->setStatus(Event::STATUS_REGISTERING);
        }
        elseif ($now > $registrationEndDate &&
            $now < $eventStartDate &&
            $status != Event::STATUS_WAITING
        ) {
            $event->setStatus(Event::STATUS_WAITING);
        }
        elseif ($now >= $eventStartDate &&
            $now <= $eventEndDate &&
            $status != Event::STATUS_ONGOING
        ) {
            $event->setStatus(Event::STATUS_ONGOING);
        }
        elseif ($now > $eventEndDate &&
            $status != Event::STATUS_END
        ) {
            $event->setStatus(Event::STATUS_END);
        }
    }
}
