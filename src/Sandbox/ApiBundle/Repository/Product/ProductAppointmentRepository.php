<?php

namespace Sandbox\ApiBundle\Repository\Product;

use Doctrine\ORM\EntityRepository;

class ProductAppointmentRepository extends EntityRepository
{
    /**
     * @param $productId
     * @param $status
     *
     * @return mixed
     */
    public function countProductAppointment(
        $productId,
        $status = null
    ) {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where('a.productId = :productId')
            ->setParameter('productId', $productId);

        if (!is_null($status)) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return (int) $query->getQuery()->getSingleScalarResult();
    }
}
