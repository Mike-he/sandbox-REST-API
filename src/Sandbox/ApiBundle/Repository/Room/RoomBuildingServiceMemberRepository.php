<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

class RoomBuildingServiceMemberRepository extends EntityRepository
{
    /**
     * @param RoomBuilding $building
     *
     * @return array
     */
    public function getServiceMembersByCompany(
        $companyId,
        $userId
    ) {
        $query = $this->createQueryBuilder('s')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'b.id = s.buildingId')
            ->select('s.buildingId')
            ->where('b.companyId = :companyId')
            ->andWhere('s.userId = :userId')
            ->setParameter('companyId', $companyId)
            ->setParameter('userId', $userId);

        return $query->getQuery()->getScalarResult();
    }
}
