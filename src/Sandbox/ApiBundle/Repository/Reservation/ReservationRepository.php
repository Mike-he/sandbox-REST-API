<?php

namespace Sandbox\ApiBundle\Repository\Reservation;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Reservation\Reservation;

class ReservationRepository extends EntityRepository
{
    /**
     * @param $userId
     * @return array
     */
    public function getReservationByUserId($userId)
    {
        $query = $this->createQueryBuilder('re')
            ->where('re.userId = :userId')
            ->setParameter('userId', $userId);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $user
     * @param $admin
     * @param $productIds
     * @param $contectName
     * @param $phone
     * @param $serialNumber
     * @param $viewTime
     * @param $status
     * @param $creationDate
     * @param $modificationDate
     * @return array
     */
    public function findBySearch(
        $user,
        $admin,
        $phone,
        $contectName,
        $serialNumber,
        $productIds,
        $viewTime,
        $status,
        $creationDate,
        $modificationDate
    ) {
        $query = $this->createQueryBuilder('re')
            ->leftJoin('SandboxApiBundle:User\UserProfile','up','WITH','up.userId = re.userId')
            ->where('re.id != :id')
            ->setParameter('id','null')
           ;

        if(!is_null($admin)){
            $query->andWhere('up.name LIKE :name')
                ->setParameter('name',$admin.'%');
        }

        if(!is_null($user)){
            $query->andWhere('up.name LIKE :name')
                ->setParameter('name',$user.'%');
        }

        if(!is_null($contectName)){
            $query->andWhere('re.contectName LIKE :contectName')
                ->setParameter('contectName',$contectName.'%')
            ;
        }

        if(!is_null($phone)){
            $query->andWhere('re.phone LIKE :phone')
                ->setParameter('phone',$phone.'%');
        }

        if(!is_null($serialNumber)){
            $query->andWhere('re.serialNumber LIKE :serialNumber')
                ->setParameter('serialNumber',$serialNumber.'%');
        }

        if(!is_null($viewTime)) {
            $query->andWhere('re.viewTime = :viewTime')
                ->setParameter('viewTime', $viewTime);
        }

        if(!is_null($productIds)){
            $query->andWhere('re.productId in (:productIds)')
                ->setParameter('productIds', $productIds);
        }

        if(!empty($creationDate)){
            $query->andWhere('creationDate = :creationDate')
                ->setParameter('creationDate', $creationDate);
        }

        if(!empty($modificationDate)) {
            $query->andWhere('modificationDate = :modificationDate')
                ->setParameter('modificationDate', $modificationDate);
        }

        if(!is_null($status)){
            $query->andWhere('status = :status')
                ->setParameter('status',$status);
        }
        return $query->getQuery()->getResult();
    }

    /**
     * @param $productIds
     * @return array
     */
    public function findUngrabedReservation($productIds)
    {
        $query = $this->createQueryBuilder('re')
            ->where('re.status = :status')
            ->andWhere('re.productId in (:productIds)')
            ->setParameter('status', Reservation::UNGRABED)
            ->setParameter('productIds',$productIds)
            ->orderBy('re.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $productId
     * @return array
     */
    public function findByUserAndProduct($userId,$productId)
    {
        $query = $this->createQueryBuilder('re')
            ->where('re.userId = :userId')
            ->andWhere('re.productId = :productId')
            ->setParameter('userId', $userId)
            ->setParameter('productId',$productId);

        return $query->getQuery()->getResult();
    }



}