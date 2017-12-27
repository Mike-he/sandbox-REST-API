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
                    st.name as type
                '
            )
            ->leftJoin('SandboxApiBundle:Service\ServiceType','st','WITH','st.id = s.typeId')
            ->where('s.salesCompanyId = :salesCompanyId')
            ->setParameter('salesCompanyId', $salesCompanyId);

        if(!is_null($type)) {
            $query->andWhere('st.id = :id')
                ->andWhere('s.isSaved = FALSE')
                ->setParameter('id', $type);
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('s.visible = :visible')
                ->setParameter('visible', $visible);
        }

        $query->orderBy('s.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}