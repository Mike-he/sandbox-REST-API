<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomFloor;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RoomRepository extends EntityRepository
{
    const ROOM_STATUS_USE = 'use';

    /**
     * Get list of orders for admin.
     *
     * @param String       $type
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
        $type,
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

        // filter by isDeleted
        $query->where('r.isDeleted = FALSE');

        // filter by type
        if (!is_null($type) && !empty($type)) {
            $query->andwhere('r.type = :type');
            $parameters['type'] = $type;
            $notFirst = true;
        }

        // filter by order status
        if (!is_null($status)) {
            if ($status == self::ROOM_STATUS_USE) {
                $where = '
                    (
                        r.orderStartDate <= :now
                        AND
                        r.orderEndDate > :now
                    )
                ';
            } else {
                $where = '
                    r.id NOT IN (
                        SELECT rv.id FROM SandboxApiBundle:Room\RoomView rv
                        WHERE
                        rv.orderStartDate <= :now
                        AND
                        rv.orderEndDate > :now
                    )
                ';
            }
            $this->addWhereQuery($query, $notFirst, $where);
            $now = new \DateTime();
            $parameters['now'] = $now;
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

        // filter by floor
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
            ->andWhere('r.isDeleted = FALSE')
            ->setParameter('roomId', $roomId);

        return $query->getQuery()->getResult();
    }

    /**
     * check room usage status.
     *
     * @param $roomId
     *
     * @return array
     */
    public function getRoomUsageStatus(
        $roomId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('r')
            ->select('
                o.userId,
                v.name,
                v.email,
                v.phone,
                o.startDate,
                o.endDate
            ')
            ->leftJoin('SandboxApiBundle:Product\Product', 'p', 'WITH', 'r.id = p.roomId')
            ->leftJoin('SandboxApiBundle:Order\ProductOrder', 'o', 'WITH', 'p.id = o.productId')
            ->leftJoin('SandboxApiBundle:User\UserView', 'v', 'WITH', 'o.userId = v.id')
            ->where('o.startDate <= :now AND o.endDate >= :now')
            ->andWhere('r.id = :roomId')
            ->setParameter('now', $now)
            ->setParameter('roomId', $roomId);

        return $query->getQuery()->getResult();
    }

    /**
     * @param RoomFloor $floor
     * @param String    $type
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getNotProductedRooms(
        $floor,
        $type
    ) {
        if (is_null($type)) {
            throw new BadRequestHttpException();
        }

        if ($type != 'fixed') {
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
        } else {
            $query = $this->createQueryBuilder('r')
                ->where('r.floor = :floor')
                ->andWhere('
                    r.id IN (
                        SELECT f.roomId
                        FROM SandboxApiBundle:Room\RoomFixed f
                        WHERE f.roomId = r.id
                        AND f.available = true
                    )
                ')
                ->setParameter('floor', $floor);
        }
        $query = $query->andWhere('r.type = :type')
            ->andWhere('r.isDeleted = FALSE')
            ->setParameter('type', $type);

        $query = $query->orderBy('r.id', 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param Array        $types
     * @param RoomCity     $city
     * @param RoomBuilding $building
     * @param String       $sortBy
     * @param String       $direction
     *
     * @return array
     */
    public function getProductedRooms(
        $types,
        $city,
        $building,
        $sortBy,
        $direction
    ) {
        $notFirst = false;
        $parameters = [];

        $query = $this->createQueryBuilder('r')
            ->Where('
                r.id IN (
                    SELECT p.roomId
                    FROM SandboxApiBundle:Product\Product p
                    WHERE p.roomId = r.id
                    AND p.visible = true
                )
            ');

        // filter by types
        if (!is_null($types) && !empty($types)) {
            $query->andwhere('r.type IN (:types)');
            $parameters['types'] = $types;
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

        //sort method
        if (!is_null($sortBy)) {
            $query->orderBy('r.'.$sortBy, $direction);
        }

        //set all parameters
        if ($notFirst) {
            $query->setParameters($parameters);
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
