<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomBuildingTagBinding.
 *
 * @ORM\Table(name="RoomBuildingTagBinding")
 * @ORM\Entity
 */
class RoomBuildingTagBinding
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
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuildingTag")
     * @ORM\JoinColumn(name="tagId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $tag;

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
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param RoomBuildingTag $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * RoomBuildingTagBinding constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
