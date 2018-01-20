<?php

namespace Sandbox\ApiBundle\Repository\Company;

use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository
{
    /**
     * @param $search
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function searchCompanies(
        $search,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('c')
            ->where('c.banned = FALSE')
            ->andWhere('c.name LIKE :search')
            ->setParameter('search', $search);

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

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
        $queryStr = 'SELECT c.id FROM SandboxApiBundle:Company\Company c';

        // get company filter by user banned and authorized
        $queryStr = $queryStr.
            ' LEFT JOIN SandboxApiBundle:User\User u
              WITH c.creatorId = u.id';

        // get company ids filter by industry if any
        if (!is_null($industryIds) && !empty($industryIds)) {
            $queryStr = $queryStr.
                ' JOIN SandboxApiBundle:Company\CompanyIndustryMap cip
                  WITH c.id = cip.companyId';
        }

        $queryStr = $queryStr.
                  ' WHERE u.banned = FALSE
                  AND c.banned = FALSE';

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
     * @param $industryIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function findRandomCompaniesToPublic(
        $industryIds,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('SandboxApiBundle:User\User', 'u', 'WITH', 'c.creatorId = u.id');

        // filter by industry ids
        if (!is_null($industryIds) && !empty($industryIds)) {
            $query->leftJoin('SandboxApiBundle:Company\CompanyIndustryMap', 'cip', 'WITH', 'c.id = cip.companyId')
                ->andWhere('cip.industryId IN (:industryIds)')
                ->setParameter('industryIds', $industryIds);
        }

        $query->where('u.banned = FALSE')
            ->andWhere('c.banned = FALSE')
            ->orderBy('c.modificationDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
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
                  AND u.banned = FALSE
                  AND c.banned = FALSE
                  ORDER BY field
                '
            )
            ->setParameter('buildingIds', $buildingIds);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function findMyCompanies($userId)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                '
                  SELECT c
                  FROM SandboxApiBundle:Company\Company c
                  LEFT JOIN SandboxApiBundle:Company\CompanyMember cm
                  WITH c.id = cm.companyId
                  LEFT JOIN SandboxApiBundle:User\User u
                  WITH c.creatorId = u.id
                  WHERE
                    cm.userId = :userId
                    AND u.banned = FALSE
                '
            )
            ->setParameter('userId', $userId);

        return $query->getResult();
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function getVerifyCompanies(
        $query
    ) {
        $queryBuilder = $this->createQueryBuilder('c')
            ->where('c.name LIKE :query')
            ->setParameter('query', $query.'%');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $keyword
     * @param $keywordSearch
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public function getCompanies(
        $keyword,
        $keywordSearch,
        $limit,
        $offset
    ) {
        $query = $this->createQueryBuilder('c')
            ->where('1=1');

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'name':
                    $query->andWhere('c.name LIKE :search');
                    break;
                case 'creator_name':
                    $query->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'c.creatorId = up.userId')
                        ->andWhere('up.name LIKE :search');
                    break;
                case 'creator_phone':
                    $query->leftJoin('c.creator', 'u')
                        ->andWhere('u.phone LIKE :search');
                    break;

                default:
                    $query->andWhere('o.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        $query->orderBy('c.creationDate', 'ASC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $keyword
     * @param $keywordSearch
     *
     * @return int
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function countCompanies(
        $keyword,
        $keywordSearch
    ) {
        $query = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('1=1');

        if (!is_null($keyword) && !is_null($keywordSearch)) {
            switch ($keyword) {
                case 'name':
                    $query->andWhere('c.name LIKE :search');
                    break;
                case 'creator_name':
                    $query->leftJoin('SandboxApiBundle:User\UserProfile', 'up', 'WITH', 'c.creatorId = up.userId')
                        ->andWhere('up.name LIKE :search');
                    break;
                case 'creator_phone':
                    $query->leftJoin('c.creator', 'u')
                        ->andWhere('u.phone LIKE :search');
                    break;

                default:
                    $query->andWhere('o.orderNumber LIKE :search');
            }
            $query->setParameter('search', '%'.$keywordSearch.'%');
        }

        $result = $query->getQuery()->getSingleScalarResult();

        return (int) $result;
    }
}
