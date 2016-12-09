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

    public function countSalesProductAppointments(
        $buildingId,
        $buildingIds,
        $status,
        $search
    ) {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = a.productId')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->select('COUNT(a)')
            ->where('a.id is not null');

//        if (!is_null($buildingId)) {
//            $query->andWhere('r.buildingId = :buildingId')
//                ->setParameter('buildingId', $buildingId);
//        } else {
//            $query->andWhere('r.buildingId IN (:buildingIds)')
//                ->setParameter('buildingIds', $buildingIds);
//        }

        if (!is_null($status)) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $productId
     * @param $status
     *
     * @return mixed
     */
    public function getSalesProductAppointments(
        $buildingId,
        $buildingIds,
        $status,
        $search,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = a.productId')
            ->leftjoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId')
            ->where('a.id is not null')
            ->orderBy('a.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

//        if (!is_null($buildingId)) {
//            $query->andWhere('r.buildingId = :buildingId')
//                ->setParameter('buildingId', $buildingId);
//        } else {
//            $query->andWhere('r.buildingId IN (:buildingIds)')
//                ->setParameter('buildingIds', $buildingIds);
//        }

        if (!is_null($status)) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return $query->getQuery()->getResult();
    }
}
