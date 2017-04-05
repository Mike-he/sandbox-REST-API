<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * UserGroupDoors.
 *
 * @ORM\Table(name="user_group_doors")
 * @ORM\Entity()
 */
class UserGroupDoors
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="building_id", type="integer")
     */
    private $building;

    /**
     * @var string
     *
     * @ORM\Column(name="door_control_id", type="string", length=255)
     */
    private $doorControlId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="group_id", type="integer")
     */
    private $group;

    /**
     * @var int
     *
     * @ORM\Column(name="card_id", type="integer", nullable=true)
     */
    private $card;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param int $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return string
     */
    public function getDoorControlId()
    {
        return $this->doorControlId;
    }

    /**
     * @param string $doorControlId
     */
    public function setDoorControlId($doorControlId)
    {
        $this->doorControlId = $doorControlId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param int $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return int
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param int $card
     */
    public function setCard($card)
    {
        $this->card = $card;
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
}
