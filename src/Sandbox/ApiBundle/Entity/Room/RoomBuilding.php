<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JsonSerializable;

/**
 * RoomBuilding.
 *
 * @ORM\Table(
 *      name="RoomBuilding",
 *      indexes={
 *          @ORM\Index(name="fk_Building_cityId_idx", columns={"cityId"})
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\Room\RoomBuildingRepository"
 * )
 */
class RoomBuilding implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "admin_room",
     *      "client",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "admin_detail",
     *      "company_info",
     *      "company_basic",
     *      "feed",
     *      "admin_event",
     *      "client_event",
     *      "current_order"
     *  }
     * )
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=1024, nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="cityId", type="integer", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $cityId;

    /**
     * @ORM\ManyToOne(targetEntity="RoomCity")
     * @ORM\JoinColumn(name="cityId", referencedColumnName="id", onDelete="CASCADE")
     * @Serializer\Groups({"main"})
     **/
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "admin_room",
     *      "client",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "admin_detail",
     *      "company_info",
     *      "company_basic",
     *      "feed",
     *      "admin_event",
     *      "client_detail",
     *      "client_event",
     *      "current_order"
     *  }
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({
     *  "main",
     *  "admin_room",
     *  "client",
     *  "admin_detail",
     *  "admin_event",
     *  "client_event",
     *  "current_order"
     * })
     */
    private $address;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", precision=9, scale=6, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", precision=9, scale=6, nullable=false)
     *
     * @Serializer\Groups({"main", "admin_room", "client"})
     */
    private $lng;

    /**
     * @var int
     *
     * @ORM\Column(name="floorNumber", type="integer", nullable=false)
     */
    private $floorNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"main", "avatar"})
     */
    private $avatar;

    /**
     * @var string
     *
     * @ORM\Column(name="server", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"server"})
     */
    private $server;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @Serializer\Groups({"main"})
     */
    private $roomAttachments;

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
     * Set description.
     *
     * @param string $description
     *
     * @return RoomBuilding
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set cityId.
     *
     * @param  $cityId
     *
     * @return RoomBuilding
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId.
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return RoomBuilding
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return RoomBuilding
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set lat.
     *
     * @param float $lat
     *
     * @return RoomBuilding
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat.
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng.
     *
     * @param float $lng
     *
     * @return RoomBuilding
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng.
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set floor number.
     *
     * @param int $floorNumber
     *
     * @return RoomBuilding
     */
    public function setFloorNumber($floorNumber)
    {
        $this->floorNumber = $floorNumber;

        return $this;
    }

    /**
     * Get floor number.
     *
     * @return int
     */
    public function getFloorNumber()
    {
        return $this->floorNumber;
    }

    /**
     * Set avatar.
     *
     * @param string $avatar
     *
     * @return RoomBuilding
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar.
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param RoomCity $city
     *
     * @return RoomBuilding
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return RoomCity
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set server.
     *
     * @param string $server
     *
     * @return RoomBuilding
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get server.
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return RoomBuilding
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return RoomBuilding
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set room attachments.
     *
     * @param $roomAttachments
     *
     * @return RoomBuilding
     */
    public function setRoomAttachments($roomAttachments)
    {
        $this->roomAttachments = $roomAttachments;

        return $this;
    }

    /**
     * Get room attachments.
     *
     * @return array
     */
    public function getRoomAttachments()
    {
        return $this->roomAttachments;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
        );
    }
}
