<?php

namespace Sandbox\ApiBundle\Repository\Service;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Service\Service;

class ServiceRepository extends EntityRepository
{
    /**
     * @param $type
     * @param $visible
     * @param $salesCompanyId
     *
     * @return array
     */
    public function getSalesServices(
        $type,
        $visible,
        $salesCompanyId
    ) {
        $query = $this->createQueryBuilder('s')
            ->select(
                '
                    s as service,
                    COUNT(so.id) as purchaseNumber
                '
            )
            ->leftJoin('SandboxApiBundle:Service\ServiceOrder', 'so', 'WITH', 'so.serviceId = s.id')
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

        $query->orderBy('s.creationDate', 'DESC');

        return $query->getQuery()->getResult();
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
        $offset = null
    ) {
        $query = $this->createQueryBuilder('s')
            ->select('
                s,
                COUNT(s.id) AS HIDDEN purchase
            ')
            ->leftJoin('SandboxApiBundle:Service\ServiceOrder', 'so', 'WITH', 'so.serviceId = s.id')
            ->where('s.visible = true')
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
                    $query->leftJoin('SandboxApiBundle:Service\ViewCount', 'v', 'WITH', 'v.objectId = s.id')
                        ->andWhere('v.object = :object')
                        ->setParameter('object', 'service')
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

        return $query->getQuery()->getResult();
    }

    /**
     * @param $id
     *
     * @return int
     */
    public function getServicePurchaseNumber(
        $id
    ) {
        $query = $this->createQueryBuilder('s')
                ->select('
                    COUNT(so.id)
                ')
                ->leftJoin('SandboxApiBundle:Service\ServiceOrder', 'so', 'WITH', 'so.serviceId = s.id')
                ->where('s.id = :id')
                ->setParameter('id', $id);

        return (int) $query->getQuery()->getSingleScalarResult();
    }
}
