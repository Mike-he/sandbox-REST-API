<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Order\ProductOrder;

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
    use CommonMethod;
    use ConsumeTrait;

    protected function setStatusCompleted(
        $order
    ) {
        $order->setStatus(ProductOrder::STATUS_COMPLETED);
        $order->setModificationDate(new \DateTime('now'));

        $price = $order->getDiscountPrice();
        $userId = $order->getUserId();

        if (!$order->isRejected()
            && (ProductOrder::CHANNEL_ACCOUNT != $order->getPayChannel())
            && $price > 0
        ) {
            // set invoice amount
            $amount = $this->postConsumeBalance(
                $userId,
                $price,
                $order->getOrderNumber()
            );
        }
    }
}
