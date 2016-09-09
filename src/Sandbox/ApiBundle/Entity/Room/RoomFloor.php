<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomFloor.
 *
 * @ORM\Table(
 *      name="room_floor",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="floorNumber_buildingId", columns={"floorNumber", "buildingId"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_Floor_buildingId_idx",columns={"buildingId"})
 *      }
 * )
 * @ORM\Entity
 */
class RoomFloor
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "admin_building"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $building;

    /**
     * @var int
     *
     * @ORM\Column(name="floorNumber", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "current_order", "admin_building"})
     */
    private $floorNumber;

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
     * Set buildingId.
     *
     * @param $buildingId
     *
     * @return RoomFloor
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId.
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set floorNumber.
     *
     * @param int $floorNumber
     *
     * @return RoomFloor
     */
    public function setFloorNumber($floorNumber)
    {
        $this->floorNumber = $floorNumber;

        return $this;
    }

    /**
     * Get floorNumber.
     *
     * @return int
     */
    public function getFloorNumber()
    {
        return $this->floorNumber;
    }

    /**
     * Set building.
     *
     * @param RoomBuilding $building
     *
     * @return RoomFloor
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get building.
     *
     * @return RoomBuilding
     */
    public function getBuilding()
    {
        return $this->building;
    }
}
