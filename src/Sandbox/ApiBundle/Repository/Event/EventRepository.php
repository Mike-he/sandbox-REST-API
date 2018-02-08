<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;

class EventRepository extends EntityRepository
{
    /**
     * @param $status
     * @param $visible
     * @param $limit
     * @param $offset
     * @param string $platform
     * @param $search
     * @param $verify
     * @param $charge
     * @param $method
     * @param $sortColumn
     * @param $direction
     * @param $commnueVisible
     * @param $keyword
     * @param $keywordSearch
     * @return array
     */
    public function getEvents(
        $status,
        $visible,
        $limit,
        $offset,
        $platform = Event::PLATFORM_OFFICIAL,
        $search,
        $verify,
        $charge,
        $method,
        $commnueVisible,
        $keyword,
        $keywordSearch,
        $sortColumn,
        $direction
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('
                e as event,
                r.name as room_name,
                r.number as room_number
            ');

        $this->setEventQuery(
            $query,
            $status,
            $visible,
            $platform,
            $search,
            $verify,
            $charge,
            $method,
            $commnueVisible,
            $keyword,
            $keywordSearch
        );

        if (!is_null($sortColumn) && !is_null($direction)) {
            switch ($sortColumn) {
                case 'price':
                    $query->orderBy('e.price', $direction);
                    break;
                case 'registrations_number':
                    $query->orderBy('vc.count', $direction);
            }
        } else {
            $query->orderBy('e.creationDate', 'DESC');
        }

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    /**
     * @param $status
     * @param $visible
     * @param string $platform
     * @param $search
     * @param $verify
     * @param $charge
     * @param $method
     * @param $commnueVisible
     * @param $keyword
     * @param $keywordSearch
     * @return mixed
     */
    public function countEvents(
        $status,
        $visible,
        $platform = Event::PLATFORM_OFFICIAL,
        $search,
        $verify,
        $charge,
        $method,
        $commnueVisible,
        $keyword,
        $keywordSearch
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('count(e.id)');

        $this->setEventQuery(
            $query,
            $status,
            $visible,
            $platform,
            $search,
            $verify,
            $charge,
            $method,
            $commnueVisible,
            $keyword,
            $keywordSearch
        );

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $query
     * @param $status
     * @param $visible
     * @param string $platform
     * @param $search
     * @param $verify
     * @param $charge
     * @param $method
     * @param $commnueVisible
     * @param $keyword
     * @param $keywordSearch
     */
    private function setEventQuery(
        $query,
        $status,
        $visible,
        $platform = Event::PLATFORM_OFFICIAL,
        $search,
        $verify,
        $charge,
        $method,
        $commnueVisible,
        $keyword,
        $keywordSearch
    ) {
        $query->leftJoin('SandboxApiBundle:Room\Room',
                'r',
                'WITH',
                'r.id = e.roomId')
            ->leftJoin('SandboxApiBundle:Service\ViewCounts', 'vc', 'WITH', 'e.id = vc.objectId')
            ->where('e.isDeleted = FALSE')
            ->andWhere('e.platform = :platform')
            ->andWhere('vc.object = :event')
            ->andWhere('vc.type = :registering')
            ->setParameter('platform', $platform)
            ->setParameter('event', ViewCounts::OBJECT_EVENT)
            ->setParameter('registering', ViewCounts::TYPE_REGISTERING)
        ;

        if ($platform == Event::PLATFORM_COMMNUE) {
            $query->andWhere('e.platform = :platform')
                ->setParameter('platform', Event::PLATFORM_OFFICIAL);
        }

        // filter by status
        if (!is_null($status)) {
            $query->andWhere('e.status = :status')
                ->setParameter('status', $status);
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        if (!is_null($commnueVisible)) {
            $query->andWhere('e.commnueVisible = :commnueVisible')
                ->setParameter('commnueVisible', $visible);
        }

        if (!is_null($search)) {
            $query->andWhere('
                    e.name LIKE :search
                    OR e.publishCompany LIKE :search
                    OR e.address LIKE :search
                ')
                ->setParameter('search', '%'.$search.'%');
        }

        if (!is_null($verify)) {
            $query->andWhere('e.verify = :verify')
                ->setParameter('verify', $verify);
        }

        if (!is_null($charge)) {
            $query->andWhere('e.isCharge = :isCharge')
                ->setParameter('isCharge', $charge);
        }

        if (!is_null($method)) {
            $query->andWhere('e.registrationMethod = :registrationMethod')
                ->setParameter('registrationMethod', $method);
        }

        if(!is_null($keyword) && !is_null($keywordSearch)) {
            $columnArray = [
                'name' => 'e.name',
                'publish_company' => 'e.publishCompany',
                'address' => 'e.address',
                'sales_company' => 'sc.name'
            ];

            if($columnArray[$keyword] == 'sc.name')
                $query->leftJoin('SandboxApiBundle:SalesAdmin\SalesCompany','sc','WITH','sc.id = e.salesCompanyId');

            $query->andWhere("$columnArray[$keyword] LIKE :searchString")
                ->setParameter('searchString', $keywordSearch);
        }

        return;
    }

    /**
     * @param $eventIds
     * @param $limit
     * @param $offset
     * @param null $status
     * @param null $excludeStatus
     * @param null $sort
     * @param $platform
     * @return array
     */
    public function getAllClientEvents(
        $eventIds,
        $limit,
        $offset,
        $status = null,
        $excludeStatus = null,
        $sort = null,
        $platform = null
    ) {
        $query = $this->createQueryBuilder('e')
            ->where('e.isDeleted = FALSE');

        if ($platform == Event::PLATFORM_COMMNUE) {
            $query->andWhere('e.commnueVisible = TRUE');
        }else{
            $query->andWhere('e.visible = TRUE');
        }

        if ($eventIds) {
            $query->andWhere('e.id IN (:ids)')
                ->setParameter('ids', $eventIds);
        }

        if(!is_null($status) && !empty($status)) {
            switch ($status) {
                case 'registered':
                    $statusArray = [Event::STATUS_WAITING,Event::STATUS_ONGOING,Event::STATUS_END];
                    $query->andWhere('e.status IN (:status)')
                        ->setParameter('status', $statusArray);
                    break;
                default:
                    $query->andWhere( 'e.status = :status')
                        ->setParameter('status',$status);
                    break;
            }
        }

        if (!is_null($excludeStatus) && !empty($excludeStatus)) {
            $query->andWhere('e.status != :exclude_status')
                ->setParameter('exclude_status', $excludeStatus);
        }

        if (!is_null($sort) && !empty($sort)) {
            switch ($sort) {
                case 'registering':
                    $query
                        ->leftJoin('SandboxApiBundle:Service\ViewCounts','v','WITH','v.objectId = e.id')
                        ->andWhere('v.object = :object')
                        ->andWhere('v.type = :type')
                        ->setParameter('object', ViewCounts::OBJECT_EVENT)
                        ->setParameter('type', ViewCounts::TYPE_REGISTERING)
                        ->orderBy('v.count', 'DESC');
                    break;
                case 'view':
                    $query->leftJoin('SandboxApiBundle:Service\ViewCounts', 'v', 'WITH', 'v.objectId = e.id')
                        ->andWhere('v.object = :object')
                        ->andWhere('v.type = :type')
                        ->setParameter('object', ViewCounts::OBJECT_EVENT)
                        ->setParameter('type', ViewCounts::TYPE_VIEW)
                        ->orderBy('v.count', 'DESC');
                    break;
                default:
                    $query->orderBy('e.eventStartDate', 'DESC');
                    break;
            }
        } else {
            $query->orderBy('e.eventStartDate', 'DESC');
        }

        $query->addOrderBy('e.registrationEndDate', 'DESC');

        if (!is_null($limit) && !is_null($offset)) {
            $query->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $objectIds
     * @param $limit
     * @param $offset
     * @param $userId
     *
     * @return array
     */
    public function getFavoriteEvents(
        $objectIds,
        $limit,
        $offset,
        $userId
    ) {
        $query = $this->createQueryBuilder('e')
            ->leftJoin(
                'SandboxApiBundle:User\UserFavorite',
                'uf',
                'WITH',
                'uf.objectId = e.id'
            )
            ->where('e.isDeleted = FALSE')
            ->andWhere("uf.object = 'event'")
            ->andWhere('uf.userId = :userId')
            ->andWhere('e.visible = TRUE')
            ->setParameter('userId', $userId);

        if ($objectIds) {
            $query->andWhere('e.id IN (:ids)')
                ->setParameter('ids', $objectIds);
        }

        $query->orderBy('uf.creationDate', 'DESC')
            ->groupBy('uf.id')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getMyClientEvents(
        $userId,
        $limit,
        $offset
    ) {
        $queryStr = '
                SELECT e
                FROM SandboxApiBundle:Event\Event e
                LEFT JOIN SandboxApiBundle:Event\EventRegistration er WITH er.eventId = e.id
                WHERE e.isDeleted = FALSE
                AND e.visible = TRUE
                AND er.userId = :userId
                AND
                (
                    (
                        e.verify = FALSE
                    ) OR (
                        e.verify = TRUE
                        AND er.status = :accepted
                    )
                )
                ORDER BY er.creationDate DESC
        ';

        $query = $this->getEntityManager()
            ->createQuery($queryStr)
            ->setParameter('userId', $userId)
            ->setParameter('accepted', EventRegistration::STATUS_ACCEPTED)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query->getResult();
    }

    /*********************************** sales api *********************************/

    /**
     * @param string $status
     * @param bool   $visible
     * @param int    $salesCompanyId
     *
     * @return array
     */
    public function getSalesEvents(
        $status,
        $visible,
        $salesCompanyId,
        $search,
        $verify,
        $charge,
        $method,
        $sortColumn,
        $direction
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('
                e as event,
                r.name as room_name,
                r.number as room_number
            ')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = e.roomId')
            ->leftJoin('SandboxApiBundle:Service\ViewCounts', 'vc', 'WITH', 'e.id = vc.objectId')
            ->where('e.isDeleted = FALSE')
            ->andWhere('e.salesCompanyId = :salesCompanyId')
            ->andWhere('vc.object = :event')
            ->andWhere('vc.type = :registering')
            ->setParameter('event', ViewCounts::OBJECT_EVENT)
            ->setParameter('registering', ViewCounts::TYPE_REGISTERING)
            ->setParameter('salesCompanyId', $salesCompanyId);

        // filter by status
        if (!is_null($status)) {
            $query->andWhere('e.status = :status')
                ->setParameter('status', $status);
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        if (!is_null($search)) {
            $query->andWhere('
                    e.name LIKE :search
                    OR e.publishCompany LIKE :search
                    OR e.address LIKE :search
                ')
                ->setParameter('search', '%'.$search.'%');
        }

        if (!is_null($verify)) {
            $query->andWhere('e.verify = :verify')
                ->setParameter('verify', $verify);
        }

        if (!is_null($charge)) {
            $query->andWhere('e.isCharge = :isCharge')
                ->setParameter('isCharge', $charge);
        }

        if (!is_null($method)) {
            $query->andWhere('e.registrationMethod = :registrationMethod')
                ->setParameter('registrationMethod', $method);
        }

        if (!is_null($sortColumn) && !is_null($direction)) {
            switch ($sortColumn) {
                case 'price':
                    $query->orderBy('e.price', $direction);
                    break;
                case 'registrations_number':
                    $query->orderBy('vc.count', $direction);
            }
        } else {
            $query->orderBy('e.creationDate', 'DESC');
        }

        return $query->getQuery()->getResult();
    }
}
