<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomRepository extends EntityRepository
{
    /**
     * Get all rooms filtered.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRoomsWithFilters(
        $filtersInfo
    ) {
        $query = $this->createQueryBuilder('r')
            ->select('r')
            ->where($filtersInfo['whereQuery'])
            ->setParameters($filtersInfo['parameters'])
            ->getQuery();

        return $query;
    }

    /**
     * Get all rooms.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRooms()
    {
        $query = $this->createQueryBuilder('r')
            ->select('r')
            ->getQuery();

        return $query;
    }
}
