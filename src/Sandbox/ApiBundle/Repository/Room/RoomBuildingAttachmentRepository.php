<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomBuildingAttachmentRepository extends EntityRepository
{
    public function findRoomBuildingAttachmentByBuildingId($buildingId)
    {
        return $this->createQueryBuilder('rba')
            ->select('rba.content')
            ->where('rba.buildingId = :buildingId')
            ->setParameter('buildingId', $buildingId)
            ->orderBy('rba.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $company
     *
     * @return array
     */
    public function findAttachmentByCompany(
        $company
    ) {
        $query = $this->createQueryBuilder('rba')
            ->select('rba.content')
            ->leftJoin('rba.building', 'b')
            ->where('b.company = :company')
            ->setParameter('company', $company)
            ->orderBy('b.id', 'ASC');

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
