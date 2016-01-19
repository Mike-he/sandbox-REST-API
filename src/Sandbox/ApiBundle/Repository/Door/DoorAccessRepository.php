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
            ->select('DISTINCT d.orderId')
            ->where('d.userId = :userId')
            ->andWhere('d.buildingId = :buildingId')
            ->andWhere('d.endDate > :now')
            ->andWhere('d.access = :access')
            ->andWhere('d.action = :action')
            ->groupBy('d.orderId')
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
            ->groupBy('d.orderId')
            ->setParameter('userId', $userId)
            ->setParameter('buildingId', $buildingId)
            ->setParameter('roomId', $roomId)
            ->setParameter('now', $now)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $userId
     * @param $orderId
     *
     * @return array
     */
    public function getAddAccessByOrder(
        $userId,
        $orderId
    ) {
        $query = $this->createQueryBuilder('d')
            ->where('d.orderId = :orderId')
            ->andWhere('d.action = :action')
            ->setParameter('orderId', $orderId)
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
     * @param $orderId
     *
     * @return array
     */
    public function getAllWithoutAccess(
        $action,
        $orderId
    ) {
        $query = $this->createQueryBuilder('d')
            ->where('d.action = :action')
            ->andWhere('d.access = :access')
            ->andWhere('d.orderId = :orderId')
            ->setParameter('orderId', $orderId)
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
            ->select('DISTINCT d.orderId')
            ->where('d.buildingId = :buildingId')
            ->andWhere('d.endDate > :now')
            ->andWhere('d.access = :access')
            ->groupBy('d.orderId')
            ->setParameter('buildingId', $buildingId)
            ->setParameter('now', $now)
            ->setParameter('access', false)
            ->getQuery();

        return $query->getResult();
    }
}
