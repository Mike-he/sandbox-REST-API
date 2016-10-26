<?php

namespace Sandbox\ApiBundle\Repository\Log;

use Doctrine\ORM\EntityRepository;

/**
 * LogRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LogRepository extends EntityRepository
{
    /**
     * @param $adminId
     * @param $companyId
     * @param $module
     * @param $search
     * @param $key
     * @param $objectId
     * @param $mark
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getAdminLogs(
        $adminId,
        $startDate,
        $endDate,
        $companyId = null,
        $module = null,
        $search = null,
        $key = null,
        $objectId = null,
        $mark = null
    ) {
        $query = $this->createQueryBuilder('l')
            ->where('1=1')
            ->orderBy('l.creationDate', 'DESC');

        if (!is_null($adminId)) {
            $query->andWhere('l.adminUsername = :adminId')
                ->setParameter('adminId', $adminId);
        }

        if (!is_null($companyId) && !empty($companyId)) {
            $query->andWhere('l.salesCompanyId = :companyId')
                ->setParameter('companyId', $companyId);
        }

        if (!is_null($module) && !empty($module)) {
            $query->andWhere('l.logModule = :logModule')
                ->setParameter('logModule', $module);
        }

        if (!is_null($key) && !empty($key) && !is_null($objectId) && !empty($objectId)) {
            $query->andWhere('l.logObjectKey = :key')
                ->andWhere('l.logObjectId = :objectId')
                ->setParameter('key', $key)
                ->setParameter('objectId', $objectId);
        }

        if (!is_null($search) && !empty($search)) {
            $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesCompany', 'c', 'WITH', 'c.id = l.salesCompanyId')
                ->andWhere('
                    (l.logModule LIKE :logModule OR 
                    l.adminUsername LIKE :search OR 
                    c.name LIKE :search OR 
                    l.logAction LIKE :search)
                ')
                ->setParameter('search', '%'.$search.'%');
        }

        if (!is_null($mark)) {
            $query->andWhere('l.mark = :mark')
                ->setParameter('mark', $mark);
        }

        if (!is_null($startDate)) {
            $query->andWhere('l.creationDate >= :start')
                ->setParameter('start', $startDate);
        }

        if (!is_null($endDate)) {
            $query->andWhere('l.creationDate <= :end')
                ->setParameter('end', $endDate);
        }

        return $query->getQuery();
    }
}
