<?php

namespace Sandbox\ApiBundle\Entity\Location;

use Doctrine\ORM\Mapping as ORM;

/**
 * Floor
 *
 * @ORM\Table(name="Floor")
 * @ORM\Entity
 */
class Floor
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="floorNumber", type="integer")
     */
    private $floorNumber;

    /**
     * @var \Sandbox\ApiBundle\Entity\Location\Building
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Location\Building")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id")
     * @ORM\Column(name="buildingId", type="integer")
     */
    private $buildingId;

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
     * Set floorNumber
     *
     * @param  integer $floorNumber
     * @return Floor
     */
    public function setFloorNumber($floorNumber)
    {
        $this->floorNumber = $floorNumber;

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

    /**
     * Set buildingId
     *
     * @param  integer $buildingId
     * @return Floor
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

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
}
