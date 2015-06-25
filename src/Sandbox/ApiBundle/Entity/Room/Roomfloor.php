<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomFloor
 *
 * @ORM\Table(
 *      name="RoomFloor",
 *      indexes={
 *          @ORM\Index(name="fk_Floor_buildingId_idx",columns={"buildingId"})
 *      }
 * )
 * @ORM\Entity
 */
class Roomfloor
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=false)
     */
    private $buildingId;

    /**
     * @var integer
     *
     * @ORM\Column(name="floorNumber", type="integer", nullable=false)
     */
    private $floorNumber;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set buildingId
     *
     * @param $buildingId
     * @return RoomFloor
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingid = $buildingId;

        return $this;
    }

    /**
     * Get buildingId
     *
     * @return integer
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set floorNumber
     *
     * @param  integer   $floorNumber
     * @return RoomFloor
     */
    public function setFloorNumber($floorNumber)
    {
        $this->floornumber = $floorNumber;

        return $this;
    }

    /**
     * Get floorNumber
     *
     * @return integer
     */
    public function getFloorNumber()
    {
        return $this->floorNumber;
    }
}
