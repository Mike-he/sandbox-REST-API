<?php

namespace Sandbox\ApiBundle\Repository\Order;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Symfony\Component\Validator\Constraints\DateTime;

class OrderRepository extends EntityRepository
{
    public function checkProductForClient(
        $productId,
        $startDate,
        $endDate
    ) {
        $query = $this->createQueryBuilder('o')
            ->Where('o.productId = :productId')
            ->andWhere('o.status <> \'cancelled\'')
            ->andWhere(
                '(
                    (o.startDate <= :startDate AND o.endDate > :startDate) OR
                    (o.startDate < :endDate AND o.endDate >= :endDate)
                )'
            )
            ->setParameter('productId', $productId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get list of orders for admin.
     *
     * @param String       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param DateTime     $startDate
     * @param DateTime     $endDate
     *
     * @return array
     */
    public function getOrdersForAdmin(
        $type,
        $city,
        $building,
        $startDate,
        $endDate
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('o')
            ->select('o')
            ->innerJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'p.id = o.productId')
            ->innerJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = p.roomId');

        // filter by type
        if (!is_null($type)) {
            $query->where('r.type = :type');
            $parameters['type'] = $type;
            $notFirst = true;
        }

        // filter by city
        if (!is_null($city)) {
            $where = 'r.city = :city';
            if ($notFirst) {
                $query->andWhere($where);
            } else {
                $query->where($where);
            }
            $parameters['city'] = $city;
            $notFirst = true;
        }

        // filter by building
        if (!is_null($building)) {
            $where = 'r.building = :building';
            if ($notFirst) {
                $query->andWhere($where);
            } else {
                $query->where($where);
            }
            $parameters['building'] = $building;
            $notFirst = true;
        }

        //filter by start date
        if (!is_null($startDate)) {
            $where = 'o.startDate >= :startDate';
            if ($notFirst) {
                $query->andWhere($where);
            } else {
                $query->where($where);
            }
            $parameters['startDate'] = $startDate;
            $notFirst = true;
        }

        //filter by end date
        if (!is_null($endDate)) {
            $where = 'o.endDate <= :endDate';
            if ($notFirst) {
                $query->andWhere($where);
            } else {
                $query->where($where);
            }
            $parameters['endDate'] = $endDate;
            $notFirst = true;
        }

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
