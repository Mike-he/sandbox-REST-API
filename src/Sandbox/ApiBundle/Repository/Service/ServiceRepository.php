<?php

namespace Sandbox\ApiBundle\Repository\Service;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;

class ServiceRepository extends EntityRepository
{
    /**
     * @param $type
     * @param $visible
     * @param $salesCompanyId
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getSalesServices(
        $type,
        $visible,
        $salesCompanyId,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('s')
            ->select(
                '
                    s as service
                '
            )
            ->where('s.salesCompanyId = :salesCompanyId')
            ->setParameter('salesCompanyId', $salesCompanyId);

        if (!is_null($type)) {
            $query->andWhere('s.type = :type')
                ->andWhere('s.isSaved = FALSE')
                ->setParameter('type', $type);
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('s.visible = :visible')
                ->setParameter('visible', $visible);
        }
        $query->groupBy('s.id');

        $query->orderBy('s.creationDate', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $type
     * @param $visible
     * @param $salesCompanyId
     *
     * @return mixed
     */
    public function getSalesServiceCount(
        $type,
        $visible,
        $salesCompanyId
    ) {
        $query = $this->createQueryBuilder('s')
            ->select(
                '
                    COUNT(s.id)
                '
            )
            ->where('s.salesCompanyId = :salesCompanyId')
            ->setParameter('salesCompanyId', $salesCompanyId);

        if (!is_null($type)) {
            $query->andWhere('s.type = :type')
                ->andWhere('s.isSaved = FALSE')
                ->setParameter('type', $type);
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('s.visible = :visible')
                ->setParameter('visible', $visible);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $country
     * @param $province
     * @param $city
     * @param $dictrict
     * @param $type
     * @param $sort
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function getClientServices(
        $country,
        $province,
        $city,
        $dictrict,
        $type,
        $sort,
        $limit = null,
        $offset = null,
        $serviceIds = null
    ) {
        $now = new \DateTime();

        $query = $this->createQueryBuilder('s')
            ->select('
                s,
                COUNT(s.id) AS HIDDEN purchase
            ')
            ->leftJoin('SandboxApiBundle:Service\ServiceOrder', 'so', 'WITH', 'so.serviceId = s.id')
            ->where('s.visible = true')
            ->andWhere('s.serviceEndDate > :endDate')
            ->setParameter('endDate', $now)
            ->groupBy('s.id');

        if (!is_null($country)) {
            $query->andWhere('s.countryId = :countryId')
                ->setParameter('countryId', $country);
        }

        if (!is_null($city)) {
            $query->andWhere('s.cityId = :cityId')
                ->setParameter('cityId', $city);
        }

        if (!is_null($province)) {
            $query->andWhere('s.provinceId = :provinceId')
                ->setParameter('provinceId', $province);
        }

        if (!is_null($dictrict)) {
            $query->andWhere('s.dictrictId = :districtId')
                ->setParameter('districtId', $dictrict);
        }

        if (!is_null($type)) {
            $query->andWhere('s.type = :type')
                ->setParameter('type', $type);
        }

        if (!is_null($sort)) {
            switch ($sort) {
                case 'purchase':
                    $query->orderBy('purchase', 'DESC');
                    break;
                case 'view':
                    $query->leftJoin('SandboxApiBundle:Service\ViewCounts', 'v', 'WITH', 'v.objectId = s.id')
                        ->andWhere('v.object = :object')
                        ->setParameter('object', ViewCounts::OBJECT_SERVICE)
                        ->orderBy('v.count', 'DESC');
                    break;
                default:
                    $query->orderBy('s.creationDate', 'DESC');
                    break;
            }
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        if (!is_null($serviceIds)) {
            $query->andWhere('s.id IN (:ids)')
                ->setParameter('ids', $serviceIds);
        }

        return $query->getQuery()->getResult();
    }
}
