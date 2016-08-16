<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomBuildingTypeBinding.
 *
 * @ORM\Table(name="RoomBuildingTypeBinding")
 * @ORM\Entity
 */
class RoomBindingTypeBinding
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
     * @var RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $building;

    /**
     * @var RoomBuildingTag
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomTypes")
     * @ORM\JoinColumn(name="typeId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

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
     * @return RoomBuilding
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param RoomBuilding $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return RoomBuildingTag
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param RoomBuildingTag $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return RoomBuildingTypeBinding
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}
