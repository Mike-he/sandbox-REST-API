<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository
{
    /**
     * @param array $recordIds
     * @param array $industryIds
     * @param int   $limit
     *
     * @return array
     */
    public function findRandomCompanies(
        $recordIds,
        $industryIds,
        $limit
    ) {
        // get company ids filter by industry if any
        $queryStr = 'SELECT c.id FROM SandboxApiBundle:Company\Company c';

        if (!is_null($industryIds) && !empty($industryIds)) {
            $queryStr = $queryStr.
                ' JOIN SandboxApiBundle:Company\CompanyIndustryMap cip
                  WITH c.id = cip.companyId';
        }

        $queryStr = $queryStr.' WHERE c.id > 0';

        if (!is_null($recordIds) && !empty($recordIds)) {
            $queryStr = $queryStr.' AND c.id NOT IN (:ids)';
        }

        if (!is_null($industryIds) && !empty($industryIds)) {
            $queryStr = $queryStr.' AND cip.industryId IN (:industryIds)';
        }

        // get available company ids
        $query = $this->getEntityManager()->createQuery($queryStr);

        if (!is_null($recordIds) && !empty($recordIds)) {
            $query->setParameter('ids', $recordIds);
        }

        if (!is_null($industryIds) && !empty($industryIds)) {
            $query->setParameter('industryIds', $industryIds);
        }

        $availableIds = $query->getScalarResult();
        if (empty($availableIds)) {
            // nothing is found
            return array();
        }

        // set total
        $total = $limit;
        $idsCount = count($availableIds);
        if ($idsCount < $limit) {
            $total = $idsCount;
        }

        // get random ids
        $ids = array();
        $randElements = array_rand($availableIds, $total);
        if (is_array($randElements)) {
            foreach ($randElements as $randElement) {
                array_push($ids, $availableIds[$randElement]);
            }
        } else {
            array_push($ids, $availableIds[$randElements]);
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
     * @param int   $range
     *
     * @return array
     */
    public function findNearbyCompanies(
        $latitude,
        $longitude,
        $limit,
        $offset,
        $range = 50
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
            ->setParameter('range', $range);

        $buildingIds = $query->getResult();

        // find companies
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT c,
                  field(c.buildingId, :buildingIds) as HIDDEN field
                  FROM SandboxApiBundle:Company\Company c
                  LEFT JOIN SandboxApiBundle:User\User u
                  WITH c.creatorId = u.id
                  WHERE
                  c.buildingId IN (:buildingIds)
                  AND u.authorized = TRUE
                  ORDER BY field
                '
            )
            ->setParameter('buildingIds', $buildingIds);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}
