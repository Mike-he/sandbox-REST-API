<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomFloor;

class RoomRepository extends EntityRepository
{
    /**
     * Get list of orders for admin.
     *
     * @param String       $type
     * @param RoomCity     $city
     * @param RoomBuilding $building
     *
     * @return array
     */
    public function getRooms(
        $type,
        $city,
        $building,
        $floor,
        $status,
        $sortBy,
        $direction
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('r');

        // filter by type
        if (!is_null($type)) {
            $query->where('r.type = :type');
            $parameters['type'] = $type;
            $notFirst = true;
        }

        // filter by order status
        if (!is_null($status)) {
            $where = 'o.status = :status';
            if ($notFirst) {
                $query->andWhere($where);
            } else {
                $query->where($where);
            }
            $parameters['status'] = $status;
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

        // filter by building
        if (!is_null($floor)) {
            $where = 'r.floor = :floor';
            if ($notFirst) {
                $query->andWhere($where);
            } else {
                $query->where($where);
            }
            $parameters['floor'] = $floor;
            $notFirst = true;
        }

        if ($sortBy != 'floor') {
            $query->orderBy('r.'.$sortBy, $direction);
        } elseif ($sortBy == 'floor') {
            $query->orderBy('rf.floorNumber', $direction);
        }

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
        }

        $result = $query->getQuery();

        return $result;
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
     * Search rooms by name or number.
     *
     * @param String $search
     *
     * @return \Doctrine\ORM\Query
     */
    public function searchRooms(
        $search
    ) {
        $query = $this->createQueryBuilder('r')
            ->where('r.name LIKE :search')
            ->orWhere('r.number LIKE :search')
            ->orderBy('r.creationDate', 'DESC')
            ->setParameter('search', "%$search%");

        return $result = $query->getQuery();
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
                )
            ')
            ->setParameter('floor', $floor);
        if (!is_null($type)) {
            $query = $query->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }
}
