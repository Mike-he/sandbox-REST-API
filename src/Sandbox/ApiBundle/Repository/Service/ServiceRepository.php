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
     * @param $district
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
        $district,
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

        if (!is_null($country) && $country != 0 ) {
            $query->andWhere('s.countryId = :countryId')
                ->setParameter('countryId', $country);
        }

        if (!is_null($city) && $city != 0) {
            $query->andWhere('s.cityId = :cityId')
                ->setParameter('cityId', $city);
        }

        if (!is_null($province) && $province != 0) {
            $query->andWhere('s.provinceId = :provinceId')
                ->setParameter('provinceId', $province);
        }

        if (!is_null($district) && $district != 0) {
            $query->andWhere('s.districtId = :districtId')
                ->setParameter('districtId', $district);
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
                        ->andWhere('v.type = :type')
                        ->setParameter('object', ViewCounts::OBJECT_SERVICE)
                        ->setParameter('type', ViewCounts::TYPE_VIEW)
                        ->orderBy('v.count', 'DESC');
                    break;
                default:
                    $query->orderBy('s.creationDate', 'DESC');
                    break;
            }
        } else {
            $query->orderBy('s.creationDate', 'DESC');
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

    /**
     * @param $limit
     * @param $offset
     * @param $serviceIds
     *
     * @return array
     */
    public function getClientFavoriteServices(
        $limit,
        $offset,
        $serviceIds
    ) {
        $query = $this->createQueryBuilder('s')
            ->where('s.visible = true');

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
