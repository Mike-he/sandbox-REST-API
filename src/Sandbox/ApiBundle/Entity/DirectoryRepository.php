<?php
namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DirectoryRepository extends EntityRepository
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
                SELECT
                    d.id,
                    d.userid,
                    d.companyid,
                    d.name,
                    d.jid,
                    d.email,
                    d.phone
                FROM SandboxApiBundle:Directory d
                WHERE d.companyid IN (
                    SELECT d2.companyid
                    FROM SandboxApiBundle:Directory d2
                    WHERE d2.userid = :userId
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
                SELECT
                    d.id,
                    d.userid,
                    d.companyid,
                    d.name,
                    d.jid,
                    d.email,
                    d.phone
                FROM SandboxApiBundle:Directory d
                WHERE d.companyid IN (
                    SELECT d2.companyid
                    FROM SandboxApiBundle:Directory d2
                    WHERE d2.userid = :userId
                )
                AND (
                    d.name LIKE :search
                )
                '
            )
            ->setParameter('userId', $userId)
            ->setParameter('search', "%$search%")
            ->getResult();
    }
}
