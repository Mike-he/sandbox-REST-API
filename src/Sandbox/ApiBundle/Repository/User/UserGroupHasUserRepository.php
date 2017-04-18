<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\User\UserGroupHasUser;

class UserGroupHasUserRepository extends EntityRepository
{
    /**
     * @param $group
     * @param $type
     *
     * @return array
     */
    public function getGroupUsers(
        $group,
        $type
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('u.userId')
            ->where('u.groupId = :group')
            ->andWhere('u.type in (:type)')
            ->setParameter('group', $group)
            ->setParameter('type', $type);

        $query = $query->groupBy('u.userId');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $user
     * @param $type
     *
     * @return array
     */
    public function getGroupsByUser(
        $user,
        $type,
        $salesCompanyId
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('
                    u.groupId as id, 
                    ug.name as name,
                    u.type
                ')
            ->leftJoin('SandboxApiBundle:User\UserGroup', 'ug', 'WITH', 'ug.id = u.groupId')
            ->where('u.userId = :user')
            ->andWhere('u.type in (:type)')
            ->andWhere('ug.companyId = :companyId')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('companyId', $salesCompanyId);

        $query = $query->groupBy('u.groupId')
            ->addGroupBy('u.type');

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $group
     * @param $date
     *
     * @return array
     */
    public function findFinishedUsers(
        $group,
        $date
    ) {
        $query = $this->createQueryBuilder('u')
            ->where('u.groupId = :group')
            ->andWhere('u.endDate < :date')
            ->andWhere('u.type != :type')
            ->setParameter('group', $group)
            ->setParameter('date', $date)
            ->setParameter('type', UserGroupHasUser::TYPE_ADD);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $group
     * @param $date
     *
     * @return array
     */
    public function findValidUsers(
        $group,
        $date
    ) {
        $query = $this->createQueryBuilder('u')
            ->select('u.userId')
            ->where('u.groupId = :group')
            ->andWhere('u.startDate < :date')
            ->andWhere('u.endDate > :date')
            ->andWhere('u.type != :type')
            ->setParameter('group', $group)
            ->setParameter('date', $date)
            ->setParameter('type', UserGroupHasUser::TYPE_ADD);

        $query = $query->groupBy('u.userId');

        $result = $query->getQuery()->getResult();
        $result = array_map('current', $result);

        return $result;
    }
}
