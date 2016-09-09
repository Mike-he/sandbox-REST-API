<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * RoomBuildingServiceBinding.
 *
 * @ORM\Table(
 *     name="room_building_service_binding",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="serviceId_buildingId", columns={"serviceId", "buildingId"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_Service_buildingId_idx",columns={"buildingId"})
 *      }
 * )
 * @ORM\Entity
 */
class RoomBuildingServiceBinding
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
     * @var RoomBuildingServices
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuildingServices")
     * @ORM\JoinColumn(name="serviceId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "admin_building"})
     */
    private $service;

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
     * @return RoomBuildingServices
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param RoomBuildingServices $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return RoomBuildingServiceBinding
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
     * RoomBuildingServiceBinding constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
