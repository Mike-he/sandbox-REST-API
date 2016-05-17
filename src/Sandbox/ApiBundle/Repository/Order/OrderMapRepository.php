<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Sandbox\ApiBundle\Entity\Event\EventOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;

class OrderMapRepository extends EntityRepository
{
    /**
     * Find orderMaps.
     *
     * @return array
     */
    public function findOrderMaps()
    {
        $query = $this->createQueryBuilder('m')
            ->where('m.orderId IS NOT NULL')
            ->andWhere('
                (m.type = :productType) OR
                (m.type = :shopType) OR
                (m.type = :eventType)
            ')
            ->setParameter('productType', ProductOrder::PRODUCT_MAP)
            ->setParameter('shopType', ShopOrder::SHOP_MAP)
            ->setParameter('eventType', EventOrder::EVENT_MAP)
            ->getQuery();

        return $query->getResult();
    }
}
