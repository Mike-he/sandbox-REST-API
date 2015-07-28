<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository
{
    /**
     * @param array $industryIds
     * @param int   $limit
     *
     * @return array
     */
    public function findRandomCompanies(
        $industryIds,
        $limit
    ) {

        // get company ids filter by industry if any
        $query = 'SELECT c.id FROM SandboxApiBundle:Company\Company c';

        if (!is_null($industryIds) && !empty($industryIds)) {
            $query = $query.
                '
                    JOIN SandboxApiBundle:Company\CompanyIndustryMap cip
                    WITH c.id = cip.companyId
                    WHERE cip.industryId IN (:industryIds)
                ';
        }

        $query = $this->getEntityManager()->createQuery($query);

        if (!is_null($industryIds) && !empty($industryIds)) {
            $query->setParameter('industryIds', $industryIds);
        }

        $availableUserIds = $query->getScalarResult();
        if (empty($availableUserIds)) {
            // nothing is found
            return array();
        }

        // set total
        $total = $limit;
        $idsCount = count($availableUserIds);
        if ($idsCount < $limit) {
            $total = $idsCount;
        }

        // get random ids
        $ids = array();
        $randElements = array_rand($availableUserIds, $total);
        if (is_array($randElements)) {
            foreach ($randElements as $randElement) {
                array_push($ids, $availableUserIds[$randElement]);
            }
        } else {
            array_push($ids, $availableUserIds[$randElements]);
        }

        if (empty($ids)) {
            // nothing is found
            return array();
        }

        // get companies
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT c FROM SandboxApiBundle:Company\Company c
                  WHERE c.id IN (:ids)
                  ORDER BY c.modificationDate DESC
                '
            )
            ->setParameter('ids', $ids);

        $query->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function findNearbyCompanies(
        $latitude,
        $longitude,
        $limit,
        $offset
    ) {
        // find nearby buildings
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT rb.id,
                  (
                    6371
                    * acos(cos(radians(:latitude)) * cos(radians(rb.lat))
                    * cos(radians(rb.lng) - radians(:longitude))
                    + sin(radians(:latitude)) * sin(radians(rb.lat)))
                    ) as HIDDEN distance
                    FROM SandboxApiBundle:Room\RoomBuilding rb
                    HAVING distance < :range
                    ORDER BY distance
                '
            )
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('range', 100);

        $buildingIds = $query->getResult();

        // find companies
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT c,
                  field(c.buildingId, :buildingIds) as HIDDEN field
                  FROM SandboxApiBundle:Company\Company c

                  WHERE

                  c.buildingId IN (:buildingIds)
                  ORDER BY field
                '
            )
            ->setParameter('buildingIds', $buildingIds);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}
