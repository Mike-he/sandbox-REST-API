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
     * @param $salesCompanyId
     * @param $buildingId
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
        $salesCompanyId,
        $buildingId,
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
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'upf', 'WITH', 'upf.userId = re.adminId')
           // ->leftJoin('SandboxApiBundle:Product\ProductRentSet','prt','WITH','prt.productId = re.productId')
            ->where('re.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId)
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
                    $query->andWhere('upf.name LIKE :search');
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

        if(!is_null($buildingId)){
            $query->andWhere('re.productId in (:productIds)')
                ->setParameter('productIds', $productIds);
        }

        $query->orderBy('re.creationDate', 'DESC');
        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $salesCompanyId
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
     * @return array
     */
    public function getCountBySearch(
        $salesCompanyId,
        $keyword,
        $keywordSearch,
        $productIds,
        $status,
        $viewStart,
        $viewEnd,
        $createStart,
        $createEnd,
        $grabStart,
        $grabEnd
    ) {
        $query = $this->createQueryBuilder('re')
            ->select('COUNT(re)')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'up.userId = re.userId')
            ->leftJoin('SandboxApiBundle:User\UserProfile', 'upf', 'WITH', 'upf.userId = re.adminId')
            ->where('re.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId)
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
                    $query->andWhere('upf.name LIKE :search');
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

        if(!empty($productIds)){
            $query->andWhere('re.productId in (:productIds)')
                ->setParameter('productIds', $productIds);
        }

        return $query->getQuery()->getSingleScalarResult();
    }
    /**
     * @param  $salesCompanyId
     * @return array
     */
    public function findCompanyUngrabedReservation($salesCompanyId)
    {
        $query = $this->createQueryBuilder('re')
            ->where('re.status = :status')
            ->andWhere('re.companyId = :companyId')
            ->setParameter('status', Reservation::UNGRABED)
            ->setParameter('companyId', $salesCompanyId)
            ->orderBy('re.creationDate', 'ASC');

        return $query->getQuery()->getResult();
    }


    /**
     * @param $userId
     * @param $productId
     * @param $viewTime
     * @return array
     */
    public function getReservationFromSameUser($userId, $productId, $viewTime)
    {
        $query = $this->createQueryBuilder('re')
            ->where('re.userId = :userId')
            ->andWhere('re.productId = :productId')
            ->andWhere('re.viewTime = :viewTime')
            ->setParameter('userId', $userId)
            ->setParameter('productId', $productId)
            ->setParameter('viewTime',$viewTime);

        return $query->getQuery()->getResult();
    }
}
