<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;

class RoomCityRepository extends EntityRepository
{
    /**
     * @param $myBuildingIds
     *
     * @return array
     */
    public function getSalesRoomCityByBuilding(
        $myBuildingIds
    ) {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'c.id = b.cityId')
            ->where('b.id IN (:buildingIds)')
            ->andWhere('b.visible = TRUE')
            ->andWhere('b.isDeleted = FALSE')
            ->andWhere('b.status = :accept')
            ->setParameter('buildingIds', $myBuildingIds)
            ->setParameter('accept', RoomBuilding::STATUS_ACCEPT);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $myShopIds
     *
     * @return array
     */
    public function getSalesRoomCityByShop(
        $myShopIds
    ) {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'c.id = b.cityId')
            ->leftJoin('SandboxApiBundle:Shop\Shop', 's', 'WITH', 'b.id = s.buildingId')
            ->where('s.id IN (:shopIds)')
            ->andWhere('b.visible = TRUE')
            ->andWhere('b.isDeleted = FALSE')
            ->andWhere('b.status = :accept')
            ->andWhere('s.active = TRUE')
            ->setParameter('shopIds', $myShopIds)
            ->setParameter('accept', RoomBuilding::STATUS_ACCEPT);

        return $query->getQuery()->getResult();
    }
}
