<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

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
    const STATUS_SAVED = 'saved';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
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
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cityId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $city;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var int
     *
     * @ORM\Column(name="roomId", type="integer", nullable=true)
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $roomId;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $address;

    /**
     * @var int
     *
     * @ORM\Column(name="limitNumber", type="integer", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $limitNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registrationStartDate", type="datetime", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $registrationStartDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registrationEndDate", type="datetime", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $registrationEndDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="eventEndDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $eventEndDate;

    /**
     * @var string
     *
     * @ORM\Column(name="registrationMethod", type="string", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $registrationMethod;

    /**
     * @var bool
     *
     * @ORM\Column(name="verify", type="boolean", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $verify;

    /**
     * @var string
     *
     * @ORM\Column(name="publishCompany", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $publishCompany;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", nullable=true)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event"
     * })
     */
    private $visible = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSaved", type="boolean", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event"
     * })
     */
    private $isSaved = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDeleted", type="boolean", nullable=false)
     *
     * @Serializer\Groups({"main"})
     */
    private $isDeleted = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
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
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $attachments;

    /**
     * @var array
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $dates;

    /**
     * @var array
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $forms;

    /**
     * @var EventRegistration
     *
     * @Serializer\Groups({
     *      "main",
     *      "client_event"
     * })
     */
    private $eventRegistration;

    /**
     * @var int
     *
     * @Serializer\Groups({
     *      "main",
     *      "client_event"
     * })
     */
    private $registeredPersonNumber;

    /**
     * @var int
     *
     * @Serializer\Groups({
     *      "main",
     *      "client_event"
     * })
     */
    private $acceptedPersonNumber;

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
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
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
    public function isVerify()
    {
        return $this->verify;
    }

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return Event
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->isDeleted;
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

    /**
     * Set eventRegistration.
     *
     * @param EventRegistration $eventRegistration
     *
     * @return Event
     */
    public function setEventRegistration($eventRegistration)
    {
        $this->eventRegistration = $eventRegistration;

        return $this;
    }

    /**
     * Get eventRegistration.
     *
     * @return EventRegistration
     */
    public function getEventRegistration()
    {
        return $this->eventRegistration;
    }

    /**
     * Set registered person number.
     *
     * @param int $registeredPersonNumber
     *
     * @return Event
     */
    public function setRegisteredPersonNumber($registeredPersonNumber)
    {
        $this->registeredPersonNumber = $registeredPersonNumber;

        return $this;
    }

    /**
     * Get registered person number.
     *
     * @return int
     */
    public function getRegisteredPersonNumber()
    {
        return $this->registeredPersonNumber;
    }

    /**
     * Set accepted person number.
     *
     * @param int $acceptedPersonNumber
     *
     * @return Event
     */
    public function setAcceptedPersonNumber($acceptedPersonNumber)
    {
        $this->acceptedPersonNumber = $acceptedPersonNumber;

        return $this;
    }

    /**
     * Get accepted person number.
     *
     * @return int
     */
    public function getAcceptedPersonNumber()
    {
        return $this->acceptedPersonNumber;
    }

    /**
     * @return string
     */
    public function getPublishCompany()
    {
        return $this->publishCompany;
    }

    /**
     * @param string $publishCompany
     */
    public function setPublishCompany($publishCompany)
    {
        $this->publishCompany = $publishCompany;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return bool
     */
    public function isSaved()
    {
        return $this->isSaved;
    }

    /**
     * @param bool $isSaved
     */
    public function setIsSaved($isSaved)
    {
        $this->isSaved = $isSaved;
    }
}
