<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomFloor.
 *
 * @ORM\Table(
 *      name="RoomFloor",
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
     * @Serializer\Groups({"main", "admin_room", "client"})
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
     * @var int
     *
     * @ORM\Column(name="floorNumber", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
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
        $this->buildingid = $buildingId;

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
        $this->floornumber = $floorNumber;

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
}
