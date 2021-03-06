<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

class RoomTypesRepository extends EntityRepository
{
    /**
     * @param RoomBuilding $building
     *
     * @return array
     */
    public function getPresentRoomTypes(
        $building
    ) {
        $query = $this->createQueryBuilder('rt')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.type = rt.name')
            ->where('r.building = :building')
            ->setParameter('building', $building);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getLimitList(
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('rt')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

    /**
     * @param $keys
     *
     * @return array
     */
    public function getTypesByKeys(
        $keys
    ) {
        $query = $this->createQueryBuilder('t')
            ->where('t.name IN (:keys)')
            ->setParameter('keys', $keys);

        return $query->getQuery()->getResult();
    }
}
