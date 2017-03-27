<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Room\RoomCity;

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
            ->andWhere('c.level = :level')
            ->setParameter('level', RoomCity::LEVEL_CITY)
            ->setParameter('shopIds', $myShopIds);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $companyId
     *
     * @return array
     */
    public function getSalesRoomCityByCompanyId(
        $companyId
    ) {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'c.id = b.cityId')
            ->where('b.companyId = :companyId')
            ->andWhere('b.isDeleted = FALSE')
            ->andWhere('c.level = :level')
            ->setParameter('level', RoomCity::LEVEL_CITY)
            ->setParameter('companyId', $companyId);

        return $query->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getLocationCities()
    {
        $query = $this->createQueryBuilder('c')
            ->select('
                c as city,
                COUNT(b) as building_count
            ')
            ->leftJoin('SandboxApiBundle:Room\RoomBuilding', 'b', 'WITH', 'c.id = b.cityId')
            ->where('b.id IS NOT NULL')
            ->groupBy('c.id');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $level
     * @param $hot
     * @param $type
     *
     * @return array
     */
    public function getCities(
        $level,
        $hot,
        $type
    ) {
        $query = $this->createQueryBuilder('c')
            ->where('1=1');

        if ($level) {
            $query->andWhere('c.level = :level')
                ->setParameter('level', $level);
        }

        if ($hot) {
            $query->andWhere('c.hot = :hot')
                ->setParameter('hot', $hot);
        }

        if ($type) {
            $query->andWhere('c.type = :type')
                ->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }
}
