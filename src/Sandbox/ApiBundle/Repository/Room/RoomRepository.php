<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomFloor;

class RoomRepository extends EntityRepository
{
    /**
     * Get list of orders for admin.
     *
     * @param String       $types
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param RoomFloor    $floor
     * @param String       $status
     * @param String       $sortBy
     * @param String       $direction
     * @param String       $search
     *
     * @return array
     */
    public function getRooms(
        $types,
        $city,
        $building,
        $floor,
        $status,
        $sortBy,
        $direction,
        $search
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('r')
            ->join('SandboxApiBundle:Room\RoomFloor', 'rf', 'WITH', 'rf.id = r.floor');

        // filter by type
        if (!is_null($type)) {
            $query->where('r.type IN (:types)');
            $parameters['types'] = $types;
            $notFirst = true;
        }

        // filter by order status
        if (!is_null($status)) {
            $where = 'o.status = :status';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['status'] = $status;
            $notFirst = true;
        }

        // filter by city
        if (!is_null($city)) {
            $where = 'r.city = :city';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['city'] = $city;
            $notFirst = true;
        }

        // filter by building
        if (!is_null($building)) {
            $where = 'r.building = :building';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['building'] = $building;
            $notFirst = true;
        }

        // filter by building
        if (!is_null($floor)) {
            $where = 'r.floor = :floor';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['floor'] = $floor;
            $notFirst = true;
        }

        //search by
        if (!is_null($search)) {
            $where = 'r.name LIKE :search or r.number LIKE :search';
            $this->addWhereQuery($query, $notFirst, $where);
            $parameters['search'] = "%$search%";
            $notFirst = true;
        }

        //sort method
        switch ($sortBy) {
            case 'floor':
                $query->orderBy('rf.floorNumber', $direction);
                break;
            default:
                $query->orderBy('r.'.$sortBy, $direction);
                break;
        }

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Seek all users that rented one room.
     *
     * @param $roomId
     *
     * @return array
     */
    public function getRoomUsersUsage(
        $roomId
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('
            	r.id,
            	up.userId as user_id,
                up.name as renter_name,
                o.startDate as start_date,
                o.endDate as end_date
            ')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'o.userId = up.userId')

            ->where('up.name IS NOT NULL')
            ->andWhere('r.id = :roomId')
            ->setParameter('roomId', $roomId);

        return $query->getQuery()->getResult();
    }

    /**
     * @param RoomFloor $floor
     * @param String    $type
     *
     * @return array
     */
    public function getValidProductRooms(
        $floor,
        $type
    ) {
        $query = $this->createQueryBuilder('r')
            ->where('r.floor = :floor')
            ->andWhere('
                r.id NOT IN (
                    SELECT p.roomId
                    FROM SandboxApiBundle:Product\Product p
                    WHERE p.roomId = r.id
                    AND p.visible = true
                )
            ')
            ->setParameter('floor', $floor);
        if (!is_null($type)) {
            $query = $query->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $query
     * @param bool         $notFirst
     * @param String       $where
     */
    private function addWhereQuery(
        $query,
        $notFirst,
        $where
    ) {
        if ($notFirst) {
            $query->andWhere($where);
        } else {
            $query->where($where);
        }
    }
}
