<?php
namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CompanymemberRepository extends EntityRepository
{
    /**
     * Find all the company member visible to the given
     * userId
     */
    public function findAllVisible($userId)
    {
        return $this->getEntityManager()
            ->createQuery(
                '
                SELECT cm FROM SandboxApiBundle:Companymember cm
                WHERE cm.companyid IN (
                    SELECT cm2.companyid
                    FROM SandboxApiBundle:Companymember cm2
                    WHERE cm2.userid = :userId
                )'
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    /**
     * Find all the company member visible to given user
     * and matching search query
     */
    public function findVisibleMatchingSearch($userId, $search)
    {
        return $this->getEntityManager()
            ->createQuery(
                '
                SELECT cm FROM SandboxApiBundle:Companymember cm
                WHERE cm.companyid IN (
                    SELECT cm2.companyid
                    FROM SandboxApiBundle:Companymember cm2
                    WHERE cm2.userid = :userId
                )
                AND (
                    cm.name LIKE :search
                )
                '
            )
            ->setParameter('userId', $userId)
            ->setParameter('search', "%$search%")
            ->getResult();
    }

    public function findAllCompanyMemberNotDeleted($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                '
                SELECT cmv FROM SandboxApiBundle:CompanymemberView cmv
                LEFT JOIN  SandboxApiBundle:Companymember cm
                WITH cmv.userid = cm.userid
                WHERE cm.isdelete = 0 and cmv.companyid = :id
                '
            )->setParameter('id', $id)
            ->getResult();
    }
}
