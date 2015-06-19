<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;

/**
 * Room
 *
 * @ORM\Table(
 *      name="Room",
 *      indexes={
 *          @ORM\Index(name="fk_Room_cityId_idx", columns={"cityId"}),
 *          @ORM\Index(name="fk_Room_buildingId_idx", columns={"buildingId"}),
 *          @ORM\Index(name="fk_Room_floorId_idx", columns={"floorId"})
 *      }
 * )
 * @ORM\Entity
 */
class Room
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
     * @ORM\Column(name="cityId", type="integer", nullable=false)
     */
    private $cityId;

    /**
     * @var integer
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=false)
     */
    private $buildingId2;

    /**
     * @var integer
     *
     * @ORM\Column(name="floorId", type="integer", nullable=false)
     */
    private $floorId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=64, nullable=false)
     */
    private $number;

    /**
     * @var integer
     *
     * @ORM\Column(name="area", type="integer", nullable=false)
     */
    private $area;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     */
    private $modificationDate;

    private $officeSupplies;

    private $attachments;

    /**
     * @var integer
     *
     * @ORM\Column(name="allowedPeople", type="integer", nullable=false)
     */
    private $allowedPeople3;

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
     * @param $cityId
     * @return Room
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

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
     * Set buildingId
     *
     * @param $buildingId2
     * @return Room
     */
    public function setBuildingId($buildingId2)
    {
        $this->buildingId2 = $buildingId2;

        return $this;
    }

    /**
     * Get buildingId
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId2;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Room
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

    /**
     * Set description
     *
     * @param  string $description
     * @return Room
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set number
     *
     * @param  string $number
     * @return Room
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set area
     *
     * @param  integer $area
     * @return Room
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return integer
     */
    public function getArea()
    {
        return $this->area;
    }

    public function getAllowedPeople()
    {
        return $this->allowedPeople3;
    }

    public function setAllowedPeople($allowedPeople3)
    {
        $this->allowedPeople3 = $allowedPeople3;

        return $this;
    }

    /**
     * Set officeSupplies
     *
     * @param  integer $officeSupplies
     * @return Room
     */
    public function setOfficeSupplies($officeSupplies)
    {
        $this->officesupplies = $officeSupplies;

        return $this;
    }

    /**
     * Get officeSupplies
     *
     * @return integer
     */
    public function getOfficeSupplies()
    {
        return $this->officeSupplies;
    }

    /**
     * Set type
     *
     * @param  string $type
     * @return Room
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set creationDate
     *
     * @param  \DateTime $creationDate
     * @return Room
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modificationDate
     *
     * @param  \DateTime $modificationDate
     * @return Room
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set floorId
     *
     * @param  integer $floorId
     * @return Room
     */
    public function setFloorId($floorId)
    {
        $this->floorId = $floorId;

        return $this;
    }

    /**
     * Get floorId
     *
     * @return integer
     */
    public function getFloorId()
    {
        return $this->floorId;
    }

    /**
     * Set attachments
     *
     * @param  string $attachments
     * @return Room
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments
     *
     * @return string
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
}
