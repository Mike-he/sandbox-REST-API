<?php

namespace Sandbox\ApiBundle\Repository\Service;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Entity\Service\ServiceType;

class ServiceRepository extends EntityRepository
{
    /**
     * @param $type
     * @param $visible
     * @param $salesCompanyId
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
                    st.name as type,
                    COUNT(so.id) as purchaseNumber
                '
            )
            ->leftJoin('SandboxApiBundle:Service\ServiceOrder','so','WITH','so.serviceId = s.id')
            ->where('s.salesCompanyId = :salesCompanyId')
            ->setParameter('salesCompanyId', $salesCompanyId);

        if(!is_null($type)) {
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
}