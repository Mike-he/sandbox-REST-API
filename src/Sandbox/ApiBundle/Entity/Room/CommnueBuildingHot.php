<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventDate.
 *
 * @ORM\Table(name = "commnue_building_hot")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Room\CommnueBuildingHotRepository")
 */
class CommnueBuildingHot
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="building_id", type="integer")
     */
    private $buildingId;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param int $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }
}
