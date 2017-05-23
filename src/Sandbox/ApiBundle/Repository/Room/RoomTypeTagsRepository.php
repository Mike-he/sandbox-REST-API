<?php

namespace Sandbox\ApiBundle\Repository\Room;

use Doctrine\ORM\EntityRepository;

class RoomTypeTagsRepository extends EntityRepository
{
    /**
     * @param $typeId
     *
     * @return array
     */
    public function getRoomTypeTags(
        $typeId
    ) {
        $query = $this->createQueryBuilder('t');

        if (!is_null($typeId)) {
            $query->where('t.parentTypeId = :typeId')
                ->setParameter('typeId', $typeId);
        }

        return $query->getQuery()->getResult();
    }
}
