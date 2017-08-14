<?php

namespace Sandbox\ApiBundle\Repository\Reservation;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Reservation\Reservation;

class ReservationRepository extends EntityRepository
{
    /**
     * @param $userId
     *
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
     * @param $keyword
     * @param $keywordSearch
     * @param $productIds
     * @param $status
     * @param $viewStart
     * @param $viewEnd
     * @param $createStart
     * @param $createEnd
     * @param $grabStart
     * @param $grabEnd
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function findBySearch(
        $keyword,
        $keywordSearch,
        $productIds,
        $status,
        $viewStart,
        $viewEnd,
        $createStart,
        $createEnd,
        $grabStart,
        $grabEnd,
        $limit = null,
        $offset = null
    ) {
        $query = $this->createQueryBuilder('re')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = re.userId')
           // ->leftJoin('SandboxApiBundle:Product\ProductRentSet','prt','WITH','prt.productId = re.productId')
            ->where('re.id != :id')
            ->setParameter('id', 'null')
           ;

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'userName':
                    $query->andWhere('up.name LIKE :search');
                    break;
                case 'userPhone':
                    $query->andWhere('up.phone LIKE :search');
                    break;
                case 'contectName':
                    $query->andWhere('re.contectName LIKE :search');
                    break;
                case 'contectPhone':
                    $query->andWhere('re.contectPhone LIKE :search');
                    break;
                case 'adminName':
                    $query->andWhere('up.name LIKE :search');
                    break;
                case 'adminPhone':
                    $query->andWhere('up.phone LIKE :search');
                    break;
                default:
                    break;
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        if(!is_null($viewStart) && !is_null($viewEnd)){
            $viewStart = new \DateTime($viewStart);
            $viewStart->setTime(00, 00, 00);
            $query->andWhere('re.viewTime >= :viewStart')
                ->setParameter('viewStart', $viewStart);
            $viewEnd = new \DateTime($viewEnd);
            $viewEnd->setTime(23, 59, 59);
            $query->andWhere('re.viewTime <= :viewEnd')
                ->setParameter('viewEnd', $viewEnd);
        }

        if(!is_null($createStart) && !is_null($createEnd)){
            $createStart = new \DateTime($createStart);
            $createStart->setTime(00, 00, 00);
            $query->andWhere('re.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);
            $query->andWhere('re.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if(!is_null($grabStart) && !is_null($grabEnd)){
            $grabStart = new \DateTime($grabStart);
            $grabStart->setTime(00, 00, 00);
            $query->andWhere('re.grabDate >= :grabStart')
                ->setParameter('grabStart', $grabStart);
            $grabEnd = new \DateTime($grabEnd);
            $grabEnd->setTime(23, 59, 59);
            $query->andWhere('re.grabDate <= :grabEnd')
                ->setParameter('grabEnd', $grabEnd);
        }

        if (!is_null($status)) {
            $query->andWhere('re.status = :status')
                ->setParameter('status', $status);
        }

        $query->andWhere('re.productId in (:productIds)')
            ->setParameter('productIds', $productIds);

        $query->orderBy('re.creationDate', 'DESC');
        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $user
     * @param $admin
     * @param $phone
     * @param $contectName
     * @param $serialNumber
     * @param $productIds
     * @param $viewTime
     * @param $status
     * @param $creationDate
     * @param $modificationDate
     *
     * @return array
     */
    public function getCountBySearch(
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
            ->select(count('re'))
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = re.userId')
            // ->leftJoin('SandboxApiBundle:Product\ProductRentSet','prt','WITH','prt.productId = re.productId')
            ->where('re.id != :id')
            ->setParameter('id', 'null')
        ;

        if (!is_null($admin)) {
            $query->andWhere('up.name LIKE :name')
                ->setParameter('name', $admin.'%');
        }

        if (!is_null($user)) {
            $query->andWhere('up.name LIKE :name')
                ->setParameter('name', $user.'%');
        }

        if (!is_null($contectName)) {
            $query->andWhere('re.contectName LIKE :contectName')
                ->setParameter('contectName', $contectName.'%')
            ;
        }

        if (!is_null($phone)) {
            $query->andWhere('re.phone LIKE :phone')
                ->setParameter('phone', $phone.'%');
        }

        if (!is_null($serialNumber)) {
            $query->andWhere('re.serialNumber LIKE :serialNumber')
                ->setParameter('serialNumber', $serialNumber.'%');
        }

        if (!is_null($viewTime)) {
            $query->andWhere('re.viewTime = :viewTime')
                ->setParameter('viewTime', $viewTime);
        }

        if (!empty($productIds)) {
            $query->andWhere('re.productId in (:productIds)')
                ->setParameter('productIds', $productIds);
        }

        if (!empty($creationDate)) {
            $query->andWhere('creationDate = :creationDate')
                ->setParameter('creationDate', $creationDate);
        }

        if (!empty($modificationDate)) {
            $query->andWhere('modificationDate = :modificationDate')
                ->setParameter('modificationDate', $modificationDate);
        }

        if (!is_null($status)) {
            $query->andWhere('status = :status')
                ->setParameter('status', $status);
        }

        return $query->getQuery()->getResult();
    }
    /**
     * @param $productIds
     *
     * @return array
     */
    public function findUngrabedReservation($productIds)
    {
        $query = $this->createQueryBuilder('re')
            ->where('re.status = :status')
            ->andWhere('re.productId in (:productIds)')
            ->setParameter('status', Reservation::UNGRABED)
            ->setParameter('productIds', $productIds)
            ->orderBy('re.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userId
     * @param $productId
     *
     * @return array
     */
    public function findByUserAndProduct($userId, $productId)
    {
        $query = $this->createQueryBuilder('re')
            ->where('re.userId = :userId')
            ->andWhere('re.productId = :productId')
            ->setParameter('userId', $userId)
            ->setParameter('productId', $productId);

        return $query->getQuery()->getResult();
    }
}
