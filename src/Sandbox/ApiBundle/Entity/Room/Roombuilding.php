<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomBuilding
 *
 * @ORM\Table(
 *      name="RoomBuilding",
 *      indexes={
 *          @ORM\Index(name="fk_Building_cityId_idx", columns={"cityId"})
 *      }
 * )
 * @ORM\Entity
 */
class RoomBuilding
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
     * @ORM\Column(name="CityId", type="integer", nullable=false)
     */
    private $cityId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;


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
     * Set cityId
     *
     * @param  $cityId
     * @return RoomBuilding
     */
    public function setCityId($cityId)
    {
        $this->cityid = $cityId;

        return $this;
    }

    /**
     * Get cityId
     *
     * @return integer
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return RoomBuilding
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
