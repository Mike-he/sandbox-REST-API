<?php

namespace Sandbox\ApiBundle\Entity\Door;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * DoorDepartmentUsers
 *
 * @ORM\Table(name="door_department_users")
 * @ORM\Entity
 */
class DoorDepartmentUsers
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
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="building_server", type="string", length=255)
     */
    private $buildingServer;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;


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
     * Set userId
     *
     * @param integer $userId
     * @return DoorDepartmentUsers
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set buildingServer
     *
     * @param string $buildingServer
     * @return DoorDepartmentUsers
     */
    public function setBuildingServer($buildingServer)
    {
        $this->buildingServer = $buildingServer;

        return $this;
    }

    /**
     * Get buildingServer
     *
     * @return string 
     */
    public function getBuildingServer()
    {
        return $this->buildingServer;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return DoorDepartmentUsers
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
}
