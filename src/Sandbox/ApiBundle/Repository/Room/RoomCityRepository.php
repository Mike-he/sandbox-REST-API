<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

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
            ->andWhere('b.isDeleted = FALSE')
            ->setParameter('buildingIds', $myBuildingIds);

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
            ->andWhere('b.isDeleted = FALSE')
            ->setParameter('shopIds', $myShopIds);

        return $query->getQuery()->getResult();
    }
}
