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
     * @param $sortColumn
     * @param $direction
     *
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
        $offset = null,
        $sortColumn = null,
        $direction = null
    ) {
        $query = $this->createQueryBuilder('re')
            ->where('1=1');

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'userName':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.userId = re.userId')
                        ->andWhere('uc.companyId = :companyId')
                        ->andWhere('uc.name LIKE :search');
                    break;
                case 'userPhone':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.userId = re.userId')
                        ->andWhere('uc.companyId = :companyId')
                        ->andWhere('uc.phone LIKE :search');
                    break;
                case 'contectName':
                    $query->andWhere('re.contectName LIKE :search');
                    break;
                case 'contectPhone':
                    $query->andWhere('re.phone LIKE :search');
                    break;
                case 'adminName':
                    $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesAdminProfiles','sap','WITH','sap.userId = re.adminId')
                        ->andWhere('sap.salesCompanyId =  :companyId OR sap.salesCompanyId is null')
                        ->andWhere('sap.nickname LIKE :search');
                    break;
                case 'adminPhone':
                    $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesAdmin', 'sa', 'WITH', 'sa.userId = re.adminId')
                        ->andWhere('sa.phone LIKE :search');
                    break;
                default:
                    $query->andWhere('re.contectName LIKE :search');
                    break;
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        $query->andWhere('re.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if (!is_null($viewStart)) {
            $viewStart = new \DateTime($viewStart);
            $viewStart->setTime(00, 00, 00);
            $query->andWhere('re.viewTime >= :viewStart')
                ->setParameter('viewStart', $viewStart);
        }

        if (!is_null($viewEnd)) {
            $viewEnd = new \DateTime($viewEnd);
            $viewEnd->setTime(23, 59, 59);
            $query->andWhere('re.viewTime <= :viewEnd')
                ->setParameter('viewEnd', $viewEnd);
        }

        if (!is_null($createStart)) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(00, 00, 00);
            $query->andWhere('re.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if (!is_null($createEnd)) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);
            $query->andWhere('re.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if (!is_null($grabStart) || !is_null($grabEnd)) {
            $query->andWhere('re.status = :status')
                ->setParameter('status', 'grabbed');
            if (!empty($grabStart)) {
                $grabStart = new \DateTime($grabStart);
                $grabStart->setTime(00, 00, 00);
                $query->andWhere('re.grabDate >= :grabStart')
                    ->setParameter('grabStart', $grabStart);
            }
            if (!empty($grabEnd)) {
                $grabEnd = new \DateTime($grabEnd);
                $grabEnd->setTime(23, 59, 59);
                $query->andWhere('re.grabDate <= :grabEnd')
                    ->setParameter('grabEnd', $grabEnd);
            }
        }

        if (!is_null($status)) {
            if($status == Reservation::UNGRABED) {
                $now = new \DateTime();
                $query->andWhere('re.viewTime >= :viewTime')
                    ->setParameter('viewTime',$now);
            }
            $query->andWhere('re.status = :status')
                ->setParameter('status', $status);
        }

        if (!is_null($buildingId)) {
            $query->andWhere('re.productId in (:productIds)')
                ->setParameter('productIds', $productIds);
        }

        if (!is_null($sortColumn) && !is_null($direction)) {
            $direction = strtoupper($direction);
            $sortArray = array(
                'view_time' => 're.viewTime',
                'creation_date' => 're.creationDate',
                'grab_date' => 're.grabDate'
            );
            $query->orderBy($sortArray[$sortColumn], $direction);
        } else {
            $query->orderBy('re.viewTime', 'DESC');
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        $query->setFirstResult($offset)
            ->setMaxResults($limit);

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
     *
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
            ->where('1=1');

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'userName':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.userId = re.userId')
                        ->andWhere('uc.companyId = :companyId')
                        ->andWhere('uc.name LIKE :search');
                    break;
                case 'userPhone':
                    $query->leftJoin('SandboxApiBundle:User\UserCustomer', 'uc', 'WITH', 'uc.userId = re.userId')
                        ->andWhere('uc.companyId = :companyId')
                        ->andWhere('uc.phone LIKE :search');
                    break;
                case 'contectName':
                    $query->andWhere('re.contectName LIKE :search');
                    break;
                case 'contectPhone':
                    $query->andWhere('re.phone LIKE :search');
                    break;
                case 'adminName':
                    $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesAdminProfiles','sap','WITH','sap.userId = re.adminId')
                        ->andWhere('sap.salesCompanyId =  :companyId OR sap.salesCompanyId is null')
                        ->andWhere('sap.nickname LIKE :search');
                    break;
                case 'adminPhone':
                    $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesAdmin', 'sa', 'WITH', 'sa.userId = re.adminId')
                        ->andWhere('sa.phone LIKE :search');
                    break;
                default:
                    $query->andWhere('re.contectName LIKE :search');
                    break;
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        $query->andWhere('re.companyId = :companyId')
            ->setParameter('companyId', $salesCompanyId);

        if (!is_null($viewStart)) {
            $viewStart = new \DateTime($viewStart);
            $viewStart->setTime(00, 00, 00);
            $query->andWhere('re.viewTime >= :viewStart')
                ->setParameter('viewStart', $viewStart);
        }

        if (!is_null($viewEnd)) {
            $viewEnd = new \DateTime($viewEnd);
            $viewEnd->setTime(23, 59, 59);
            $query->andWhere('re.viewTime <= :viewEnd')
                ->setParameter('viewEnd', $viewEnd);
        }

        if (!is_null($createStart)) {
            $createStart = new \DateTime($createStart);
            $createStart->setTime(00, 00, 00);
            $query->andWhere('re.creationDate >= :createStart')
                ->setParameter('createStart', $createStart);
        }

        if (!is_null($createEnd)) {
            $createEnd = new \DateTime($createEnd);
            $createEnd->setTime(23, 59, 59);
            $query->andWhere('re.creationDate <= :createEnd')
                ->setParameter('createEnd', $createEnd);
        }

        if (!is_null($grabStart) || !is_null($grabEnd)) {
            $query->andWhere('re.status = :status')
                ->setParameter('status', 'grabbed');
            if (!empty($grabStart)) {
                $grabStart = new \DateTime($grabStart);
                $grabStart->setTime(00, 00, 00);
                $query->andWhere('re.grabDate >= :grabStart')
                    ->setParameter('grabStart', $grabStart);
            }
            if (!empty($grabEnd)) {
                $grabEnd = new \DateTime($grabEnd);
                $grabEnd->setTime(23, 59, 59);
                $query->andWhere('re.grabDate <= :grabEnd')
                    ->setParameter('grabEnd', $grabEnd);
            }
        }

        if (!is_null($status)) {
            if($status == Reservation::UNGRABED) {
                $now = new \DateTime();
                $query->andWhere('re.viewTime >= :viewTime')
                    ->setParameter('viewTime',$now);
            }
            $query->andWhere('re.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($productIds)) {
            $query->andWhere('re.productId in (:productIds)')
                ->setParameter('productIds', $productIds);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $salesCompanyId
     * @param null $time
     * @param $limit
     * @param $offset
     * @return array
     */
    public function findCompanyUngrabedReservation(
        $salesCompanyId,
        $time=null,
        $limit=null,
        $offset=null
    ) {
        $query = $this->createQueryBuilder('re')
            ->where('re.status = :status')
            ->andWhere('re.companyId = :companyId')
            ->setParameter('status', Reservation::UNGRABED)
            ->setParameter('companyId', $salesCompanyId);

        if(!is_null($time)){
            $query->andWhere('re.viewTime >= :viewTime')
                ->setParameter('viewTime',$time);
        }
            $query->orderBy('re.viewTime', 'ASC');

        if(!is_null($limit) && !is_null($offset)){
            $query->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $salesCompanyId
     * @param $time
     *
     * @return int
     */
    public function countCompanyUngrabedReservation(
        $salesCompanyId,
        $time
    ) {
        $query = $this->createQueryBuilder('re')
            ->select('COUNT(re)')
            ->where('re.status = :status')
            ->andWhere('re.companyId = :companyId')
            ->andWhere('re.viewTime >= :viewTime')
            ->setParameter('status', Reservation::UNGRABED)
            ->setParameter('companyId', $salesCompanyId)
            ->setParameter('viewTime', $time)
        ;

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * @param $userId
     * @param $productId
     * @param $viewTime
     *
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
            ->setParameter('viewTime', $viewTime);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $salesCompanyId
     * @param $adminId
     * @param $status
     * @param $grabStart
     * @param $grabEnd
     *
     * @return int
     */
    public function countReservationByAdminId(
        $salesCompanyId,
        $adminId,
        $status,
        $grabStart,
        $grabEnd
    )
    {
        $query = $this->createQueryBuilder('re')
            ->select('COUNT(re)')
            ->where('re.companyId = :companyId')
            ->andWhere('re.adminId = :adminId')
            ->setParameter('adminId', $adminId)
            ->setParameter('companyId', $salesCompanyId);

        if (!is_null($grabStart) || !empty($grabStart)) {
            $query->andWhere('re.grabDate >= :grabStart')
                ->setParameter('grabStart', $grabStart);
        }

        if (!is_null($grabEnd) || !empty($grabEnd)) {
            $query->andWhere('re.grabDate <= :grabEnd')
                ->setParameter('grabEnd', $grabEnd);
        }

        if (!is_null($status)) {
            $query->andWhere('re.status = :status')
                ->setParameter('status', $status);
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return (int)$result;
    }

    /**
     * @param $salesCompanyId
     * @param $adminId
     * @param $grabStart
     * @param $grabEnd
     * @param $status
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getMylatestGradedLists(
        $salesCompanyId,
        $adminId,
        $grabStart,
        $grabEnd,
        $status,
        $limit,
        $offset
    ){
        $query = $this->createQueryBuilder('re')
            ->where('re.companyId = :companyId')
            ->andWhere('re.adminId = :adminId')
            ->andWhere('re.grabDate >= :grabStart')
            ->andWhere('re.grabDate <= :grabEnd')
            ->andWhere('re.status = :status')
            ->setParameter('adminId', $adminId)
            ->setParameter('companyId', $salesCompanyId)
            ->setParameter('grabStart', $grabStart)
            ->setParameter('grabEnd', $grabEnd)
            ->setParameter('status', $status);

        $query->orderBy('re.viewTime','ASC');

        $query->setFirstResult($offset)
            ->setMaxResults($limit);

        return $result = $query->getQuery()->getResult();
    }
}
