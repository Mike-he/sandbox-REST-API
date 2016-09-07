<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomBuildingTypeBinding.
 *
 * @ORM\Table(
 *     name="RoomBuildingTypeBinding",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="typeId_buildingId", columns={"typeId", "buildingId"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_RoomType_buildingId_idx",columns={"buildingId"})
 *      }
 * )
 * @ORM\Entity
 */
class RoomBuildingTypeBinding
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
     *
     * @Serializer\Groups({"main", "admin_building"})
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

    /**
     * RoomBuildingTypeBinding constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
