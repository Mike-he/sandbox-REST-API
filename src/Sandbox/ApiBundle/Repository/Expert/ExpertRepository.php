<?php

namespace Sandbox\ApiBundle\Repository\Expert;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Expert\Expert;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;

class ExpertRepository extends EntityRepository
{
    /**
     * @param $field
     * @param $country
     * @param $province
     * @param $city
     * @param $district
     * @param $sort
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getExperts(
        $field,
        $country,
        $province,
        $city,
        $district,
        $sort,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('
                    distinct
                        e.id,
                        e.photo,
                        e.name,
                        e.identity,
                        e.introduction,
                        rc.name as district_name                    
                ')
            ->leftJoin('SandboxApiBundle:Room\RoomCity', 'rc', 'WITH', 'e.districtId = rc.id')
            ->where('e.banned = FALSE')
            ->andWhere('e.isService = TRUE')
            ->andWhere('e.status = :success')
            ->setParameter('success', Expert::STATUS_SUCCESS)
        ;

        if ($field) {
            $query->innerJoin('e.expertFields', 'ef')
                ->andWhere('ef.id  in (:field)')
                ->setParameter('field', $field);
        }

        if ($country) {
            $query->andWhere('e.countryId = :country')
                ->setParameter('country', $country);
        }

        if ($province) {
            $query->andWhere('e.provinceId = :province')
                ->setParameter('province', $province);
        }

        if ($city) {
            $query->andWhere('e.cityId = :city')
                ->setParameter('city', $city);
        }

        if ($district) {
            $query->andWhere('e.districtId = :district')
                ->setParameter('district', $district);
        }

        switch ($sort) {
            case 'default':
                $query->orderBy('e.creationDate', 'DESC');
                break;
            case 'view':
                $query->leftJoin('SandboxApiBundle:Service\ViewCounts', 'v', 'WITH', 'e.id = v.objectId')
                    ->andWhere('v.object = :object')
                    ->andWhere('v.type = :type')
                    ->setParameter('object', ViewCounts::OBJECT_EXPERT)
                    ->setParameter('type', ViewCounts::TYPE_VIEW)
                    ->orderBy('v.count', 'DESC');
                break;
            case 'booking':
                $query->leftJoin('SandboxApiBundle:Service\ViewCounts', 'v', 'WITH', 'e.id = v.objectId')
                    ->andWhere('v.object = :object')
                    ->andWhere('v.type = :type')
                    ->setParameter('object', ViewCounts::OBJECT_EXPERT)
                    ->setParameter('type', ViewCounts::TYPE_BOOKING)
                    ->orderBy('v.count', 'DESC');
                break;
            default:
                $query->orderBy('e.creationDate', 'DESC');
                break;
        }

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $fieldId
     *
     * @return array
     */
    public function checkExpertField(
        $fieldId
    ) {
        $query = $this->createQueryBuilder('e')
            ->innerJoin('e.expertFields', 'ef')
            ->andWhere('ef.id  = :field')
            ->setParameter('field', $fieldId);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $banned
     * @param $name
     * @param $phone
     * @param $status
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getAdminExperts(
        $banned,
        $name,
        $phone,
        $status,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('e')
            ->where('e.id > 0');

        if (!is_null($banned)) {
            $query->andWhere('e.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($name)) {
            $query->andWhere('e.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }

        if (!is_null($phone)) {
            $query->andWhere('e.phone LIKE :phone')
                ->setParameter('phone', '%'.$phone.'%');
        }

        if (!is_null($status)) {
            $query->andWhere('e.status = :status')
                ->setParameter('status', $status);
        }

        $query->orderBy('e.top', 'DESC')
            ->addOrderBy('e.creationDate', 'DESC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $banned
     * @param $name
     * @param $phone
     * @param $status
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function countAdminExperts(
        $banned,
        $name,
        $phone,
        $status
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->where('e.id > 0');

        if (!is_null($banned)) {
            $query->andWhere('e.banned = :banned')
                ->setParameter('banned', $banned);
        }

        if (!is_null($name)) {
            $query->andWhere('e.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }

        if (!is_null($phone)) {
            $query->andWhere('e.phone LIKE :phone')
                ->setParameter('phone', '%'.$phone.'%');
        }

        if (!is_null($status)) {
            $query->andWhere('e.status = :status')
                ->setParameter('status', $status);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $limit
     * @param $offset
     * @param $objectIds
     * @param $userId
     *
     * @return mixed
     */
    public function getFavoriteExperts(
        $limit,
        $offset,
        $objectIds,
        $userId
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('
                    distinct
                        e.id,
                        e.photo,
                        e.name,
                        e.identity,
                        e.introduction,
                        rc.name as district_name                    
                ')
            ->leftJoin('SandboxApiBundle:Room\RoomCity', 'rc', 'WITH', 'e.districtId = rc.id')
            ->leftJoin(
                'SandboxApiBundle:User\UserFavorite',
                'uf',
                'WITH',
                'uf.objectId = e.id'
            )
            ->andWhere("uf.object = 'expert'")
            ->andWhere('uf.userId = :userId')
            ->setParameter('userId', $userId);

        if ($objectIds) {
            $query->andWhere('e.id IN (:ids)')
                ->setParameter('ids', $objectIds);
        }

        $query->orderBy('uf.creationDate', 'DESC')
            ->groupBy('uf.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }
}
