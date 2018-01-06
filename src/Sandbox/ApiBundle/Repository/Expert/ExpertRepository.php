<?php

namespace Sandbox\ApiBundle\Repository\Expert;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;

class ExpertRepository extends EntityRepository
{
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
            ->where('e.banned = 0')
            ->andWhere('e.isService = 1')
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
}
