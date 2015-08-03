<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomCity;

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
}
