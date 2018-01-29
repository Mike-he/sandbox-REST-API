<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

class CommnueBuildingHotRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function countHots()
    {
        $query = $this->createQueryBuilder('cbh')
            ->select('COUNT(cbh.id)');

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getHotCommunities()
    {
        $query = $this->createQueryBuilder('cbh')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'rb', 'WITH', 'cbh.buildingId = rb.id')
            ->select('cbh.buildingId')
            ->where('rb.commnueStatus != :commnueStatus')
            ->setParameter('commnueStatus', RoomBuilding::FREEZON);

        $ids = $query->getQuery()->getScalarResult();

        return array_unique(array_map('current', $ids));
    }
}
