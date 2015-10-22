<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event.
 *
 * @ORM\Table(name = "Event")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Event\EventRepository")
 */
class Event
{
    const REGISTRATION_METHOD_ONLINE = 'online';
    const REGISTRATION_METHOD_OFFLINE = 'offline';

    const STATUS_ONGOING = 'ongoing';
    const STATUS_END = 'end';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="cityId", type="integer", nullable=false)
     */
    private $cityId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cityId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $city;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=false)
     */
    private $buildingId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="buildingId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $building;

    /**
     * @var int
     *
     * @ORM\Column(name="roomId", type="integer", nullable=true)
     */
    private $roomId;

    /**
     * @var int
     *
     * @ORM\Column(name="limitNumber", type="integer", nullable=false)
     */
    private $limitNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registrationStartDate", type="datetime", nullable=false)
     */
    private $registrationStartDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registrationEndDate", type="datetime", nullable=false)
     */
    private $registrationEndDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="eventEndDate", type="datetime", nullable=false)
     */
    private $eventEndDate;

    /**
     * @var string
     *
     * @ORM\Column(name="registrationMethod", type="string", nullable=false)
     */
    private $registrationMethod;

    /**
     * @var bool
     *
     * @ORM\Column(name="verify", type="boolean", nullable=false)
     */
    private $verify;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     */
    private $visible = true;

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

    /**
     * @var EventAttachment
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Event\EventAttachment",
     *      mappedBy="event",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="eventId")
     */
    private $attachments;

    /**
     * @var EventDate
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Event\EventDate",
     *      mappedBy="event",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="eventId")
     */
    private $dates;

    /**
     * @var EventForm
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Event\EventForm",
     *      mappedBy="event",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="eventId")
     */
    private $forms;

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
     * Set name.
     *
     * @param string $name
     *
     * @return Event
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
     * Set description.
     *
     * @param string $description
     *
     * @return Event
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
     * @param int $cityId
     *
     * @return Event
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
     * Set city.
     *
     * @param $city
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return Event
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set buildingId.
     *
     * @param int $buildingId
     *
     * @return Event
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
     * Set building.
     *
     * @param $building
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get building.
     *
     * @return Event
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Set roomId.
     *
     * @param int $roomId
     *
     * @return Event
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId.
     *
     * @return int
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set limitNumber.
     *
     * @param int $limitNumber
     *
     * @return Event
     */
    public function setLimitNumber($limitNumber)
    {
        $this->limitNumber = $limitNumber;

        return $this;
    }

    /**
     * Get limitNumber.
     *
     * @return int
     */
    public function getLimitNumber()
    {
        return $this->limitNumber;
    }

    /**
     * Set registrationStartDate.
     *
     * @param \DateTime $registrationStartDate
     *
     * @return Event
     */
    public function setRegistrationStartDate($registrationStartDate)
    {
        $this->registrationStartDate = $registrationStartDate;

        return $this;
    }

    /**
     * Get registrationStartDate.
     *
     * @return \DateTime
     */
    public function getRegistrationStartDate()
    {
        return $this->registrationStartDate;
    }

    /**
     * Set registrationEndDate.
     *
     * @param \DateTime $registrationEndDate
     *
     * @return Event
     */
    public function setRegistrationEndDate($registrationEndDate)
    {
        $this->registrationEndDate = $registrationEndDate;

        return $this;
    }

    /**
     * Get registrationEndDate.
     *
     * @return \DateTime
     */
    public function getRegistrationEndDate()
    {
        return $this->registrationEndDate;
    }

    /**
     * Set registrationMethod.
     *
     * @param string $registrationMethod
     *
     * @return Event
     */
    public function setRegistrationMethod($registrationMethod)
    {
        $this->registrationMethod = $registrationMethod;

        return $this;
    }

    /**
     * Get registrationMethod.
     *
     * @return string
     */
    public function getRegistrationMethod()
    {
        return $this->registrationMethod;
    }

    /**
     * Set verify.
     *
     * @param bool $verify
     *
     * @return Event
     */
    public function setVerify($verify)
    {
        $this->verify = $verify;

        return $this;
    }

    /**
     * Get verify.
     *
     * @return bool
     */
    public function getVerify()
    {
        return $this->verify;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     *
     * @return Event
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set eventEndDate.
     *
     * @param \DateTime $eventEndDate
     *
     * @return Event
     */
    public function setEventEndDate($eventEndDate)
    {
        $this->eventEndDate = $eventEndDate;

        return $this;
    }

    /**
     * Get eventEndDate.
     *
     * @return \DateTime
     */
    public function getEventEndDate()
    {
        return $this->eventEndDate;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Event
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
     * @return Event
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
     * @param EventAttachment $attachments
     *
     * @return Event
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @return EventAttachment
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param EventDate $dates
     *
     * @return Event
     */
    public function setDates($dates)
    {
        $this->dates = $dates;

        return $this;
    }

    /**
     * @return EventDate
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * @param EventForm $forms
     *
     * @return Event
     */
    public function setForms($forms)
    {
        $this->forms = $forms;

        return $this;
    }

    /**
     * @return EventForm
     */
    public function getForms()
    {
        return $this->forms;
    }
}