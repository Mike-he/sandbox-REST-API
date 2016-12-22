<?php

namespace Sandbox\ApiBundle\Repository\Door;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;

class DoorAccessRepository extends EntityRepository
{
    /**
     * @param $userId
     *
     * @return array
     */
    public function getBuildingIds(
        $userId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->select('DISTINCT d.buildingId')
            ->where('d.userId = :userId')
            ->andWhere('d.endDate > :now')
            ->andWhere('d.access = :access')
            ->andWhere('d.action = :action')
            ->setParameter('action', DoorAccessConstants::METHOD_ADD)
            ->setParameter('userId', $userId)
            ->setParameter('now', $now)
            ->setParameter('access', false)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $buildingId
     *
     * @return array
     */
    public function getOrdersByBuilding(
        $userId,
        $buildingId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->select('DISTINCT d.accessNo')
            ->where('d.userId = :userId')
            ->andWhere('d.buildingId = :buildingId')
            ->andWhere('d.endDate > :now')
            ->andWhere('d.access = :access')
            ->andWhere('d.action = :action')
            ->groupBy('d.accessNo')
            ->setParameter('action', DoorAccessConstants::METHOD_ADD)
            ->setParameter('userId', $userId)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('now', $now)
            ->setParameter('access', false)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $buildingId
     * @param $doorId
     *
     * @return array
     */
    public function getDoorsByDoorId(
        $userId,
        $buildingId,
        $doorId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->where('d.userId = :userId')
            ->andWhere('d.buildingId = :buildingId')
            ->andWhere('d.doorId = :doorId')
            ->andWhere('d.endDate > :now')
            ->setParameter('userId', $userId)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('doorId', $doorId)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $buildingId
     * @param $roomId
     *
     * @return array
     */
    public function getAccessByRoom(
        $userId,
        $buildingId,
        $roomId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->where('d.userId = :userId')
            ->andWhere('d.buildingId = :buildingId')
            ->andWhere('d.roomId = :roomId')
            ->andWhere('d.endDate > :now')
            ->groupBy('d.accessNo')
            ->setParameter('userId', $userId)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('roomId', $roomId)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $accessNo
     *
     * @return array
     */
    public function getAddAccessByOrder(
        $userId,
        $accessNo
    ) {
        $query = $this->createQueryBuilder('d')
            ->where('d.accessNo = :accessNo')
            ->andWhere('d.action = :action')
            ->setParameter('accessNo', $accessNo)
            ->setParameter('action', DoorAccessConstants::METHOD_ADD);

        if (!is_null($userId)) {
            $query = $query->andWhere('d.userId = :userId')
                ->setParameter('userId', $userId);
        }
        $query = $query->getQuery();

        return $query->getResult();
    }

    /**
     * @param $action
     * @param $accessNo
     *
     * @return array
     */
    public function getAllWithoutAccess(
        $action,
        $accessNo
    ) {
        $query = $this->createQueryBuilder('d')
            ->where('d.action = :action')
            ->andWhere('d.access = :access')
            ->andWhere('d.accessNo = :accessNo')
            ->setParameter('accessNo', $accessNo)
            ->setParameter('access', false)
            ->setParameter('action', $action)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $buildingId
     *
     * @return array
     */
    public function getAccessByBuilding(
        $buildingId
    ) {
        $now = new \DateTime();
        $query = $this->createQueryBuilder('d')
            ->select('DISTINCT d.accessNo')
            ->where('d.buildingId = :buildingId')
            ->andWhere('d.endDate > :now')
            ->andWhere('d.access = :access')
            ->groupBy('d.accessNo')
            ->setParameter('buildingId', $buildingId)
            ->setParameter('now', $now)
            ->setParameter('access', false)
            ->getQuery();

        return $query->getResult();
    }
}
