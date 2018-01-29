<?php

namespace Sandbox\ApiBundle\Repository\Event;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Event\EventRegistration;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;

class EventRepository extends EntityRepository
{
    /**
     * @param string $status
     * @param bool   $visible
     * @param int    $limit
     * @param int    $offset
     * @param        $platform
     *
     * @return array
     */
    public function getEvents(
        $status,
        $visible,
        $limit,
        $offset,
        $platform = Event::PLATFORM_OFFICIAL
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('
                e as event,
                r.name as room_name,
                r.number as room_number
            ')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = e.roomId')
            ->where('e.isDeleted = FALSE')
            ->andWhere('e.platform = :platform')
            ->setParameter('platform', $platform);

        // filter by status
        if (!is_null($status)) {
            switch ($status) {
                case Event::STATUS_PREHEATING:
                    $query->andWhere('e.registrationStartDate > :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_REGISTERING:
                    $query->andWhere('e.registrationStartDate <= :now')
                        ->andWhere('e.registrationEndDate >= :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_ONGOING:
                    $query->andWhere('e.eventStartDate <= :now')
                        ->andwhere('e.eventEndDate >= :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_END == $status:
                    $query->andwhere('e.eventEndDate < :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_SAVED == $status:
                    $query->andWhere('e.isSaved = TRUE');
                    break;
                default:
                    break;
            }
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        $query->orderBy('e.creationDate', 'DESC');

        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery()->getResult();
    }

    public function countEvents(
        $status,
        $visible,
        $platform = Event::PLATFORM_OFFICIAL
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = e.roomId')
            ->where('e.isDeleted = FALSE')
            ->where('e.platform = :platform')
            ->setParameter('platform', $platform);

        // filter by status
        if (!is_null($status)) {
            switch ($status) {
                case Event::STATUS_PREHEATING:
                    $query->andWhere('e.registrationStartDate > :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_REGISTERING:
                    $query->andWhere('e.registrationStartDate <= :now')
                        ->andWhere('e.registrationEndDate >= :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_ONGOING:
                    $query->andWhere('e.eventStartDate <= :now')
                        ->andwhere('e.eventEndDate >= :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_END == $status:
                    $query->andwhere('e.eventEndDate < :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_SAVED == $status:
                    $query->andWhere('e.isSaved = TRUE');
                    break;
                default:
                    break;
            }
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $eventIds
     * @param $limit
     * @param $offset
     * @param null $status
     * @param null $sort
     * @return array
     */
    public function getAllClientEvents(
        $eventIds,
        $limit,
        $offset,
        $status = null,
        $sort = null
    ) {
        $query = $this->createQueryBuilder('e')
            ->where('e.isDeleted = FALSE')
            ->andWhere('e.visible = TRUE');

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
                $query->orderBy('e.registrationEndDate', 'DESC')
                    ->addOrderBy('e.eventStartDate', 'DESC');
                break;
        }

        $query->setFirstResult($offset)
            ->setMaxResults($limit);

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
        $salesCompanyId
    ) {
        $query = $this->createQueryBuilder('e')
            ->select('
                e as event,
                r.name as room_name,
                r.number as room_number
            ')
            ->leftJoin('SandboxApiBundle:Room\Room', 'r', 'WITH', 'r.id = e.roomId')
            ->where('e.isDeleted = FALSE')
            ->andWhere('e.salesCompanyId = :salesCompanyId')
            ->setParameter('salesCompanyId', $salesCompanyId);

        // filter by status
        if (!is_null($status)) {
            switch ($status) {
                case Event::STATUS_PREHEATING:
                    $query->andWhere('e.registrationStartDate > :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_REGISTERING:
                    $query->andWhere('e.registrationStartDate <= :now')
                        ->andWhere('e.registrationEndDate >= :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_ONGOING:
                    $query->andWhere('e.eventStartDate <= :now')
                        ->andwhere('e.eventEndDate >= :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_END == $status:
                    $query->andwhere('e.eventEndDate < :now')
                        ->andWhere('e.isSaved = FALSE')
                        ->setParameter('now', new \DateTime('now'));
                    break;
                case Event::STATUS_SAVED == $status:
                    $query->andWhere('e.isSaved = TRUE');
                    break;
                default:
                    break;
            }
        }

        // filter by visible
        if (!is_null($visible)) {
            $query->andWhere('e.visible = :visible')
                ->setParameter('visible', $visible);
        }

        $query->orderBy('e.creationDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}
