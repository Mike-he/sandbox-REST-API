<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Event\EventOrder;
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

    /**
     * @param ProductOrder $order
     */
    protected function setProductOrderStatusCompleted(
        $order
    ) {
        $order->setStatus(ProductOrder::STATUS_COMPLETED);
        $order->setModificationDate(new \DateTime('now'));

        //update user bean
        $this->getContainer()->get('sandbox_api.bean')->postBeanChange(
            $order->getUserId(),
            $order->getPrice(),
            $order->getOrderNumber(),
            Parameter::KEY_BEAN_PRODUCT_ORDER
        );

        //update invitee bean
        $user = $this->getContainer()->get('doctrine')
            ->getRepository('SandboxApiBundle:User\User')
            ->find($order->getUserId());

        if ($user->getInviterId()) {
            $this->getContainer()->get('sandbox_api.bean')->postBeanChange(
                $user->getInviterId(),
                $order->getPrice(),
                $order->getOrderNumber(),
                Parameter::KEY_BEAN_INVITEE_PRODUCT_ORDER
            );
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
