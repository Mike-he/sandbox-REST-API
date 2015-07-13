<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use JMS\Serializer\Annotation as Serializer;

/**
 * User Profile.
 *
 * @ORM\Table(name="UserProfile")
 * @ORM\Entity
 */
class UserProfile
{
    const DEFAULT_GENDER_OTHER = 'other';
    const DEFAULT_GENDER_MALE = 'male';
    const DEFAULT_GENDER_FEMALE = 'female';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "admin_order"
     *  }
     * )
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=false)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "admin_order"
     *  }
     * )
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "admin_order"
     *  }
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="jobTitle", type="string", length=64, nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member"
     *  }
     * )
     */
    private $jobTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=false)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member"
     *  }
     * )
     */
    private $gender = self::DEFAULT_GENDER_OTHER;

    /**
     * @var string
     *
     * @ORM\Column(name="dateOfBirth", type="string", length=16, nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic"
     *  }
     * )
     */
    private $dateOfBirth;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "admin_order"
     *  }
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=128, nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "admin_order"
     *  }
     * )
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="aboutMe", type="string", nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "member"
     *  }
     * )
     */
    private $aboutMe;

    /**
     * @var string
     *
     * @ORM\Column(name="skill", type="string", nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "member"
     *  }
     * )
     */
    private $skill;

    /**
     * @var string
     *
     * @ORM\Column(name="sinaWeibo", type="string", length=128, nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger"
     *  }
     * )
     */
    private $sinaWeibo;

    /**
     * @var string
     *
     * @ORM\Column(name="tencentWeibo", type="string", length=128, nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger"
     *  }
     * )
     */
    private $tencentWeibo;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="string", length=128, nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger"
     *  }
     * )
     */
    private $facebook;

    /**
     * @var string
     *
     * @ORM\Column(name="linkedin", type="string", length=128, nullable=true)
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger"
     *  }
     * )
     */
    private $linkedin;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User"))
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     **/
    private $user;

    /**
     * @var RoomBuilding
     *
     * @ORM\OneToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding"))
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id")
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member"
     *  }
     * )
     **/
    private $building;

    /**
     * @var array
     */
    private $hobbyIds;

    /**
     * @var array
     *
     * @Serializer\Groups(
     *  {
     *      "profile",
     *      "profile_stranger",
     *      "profile_basic_stranger"
     *  }
     * )
     */
    private $hobbies;

    /**
     * @var array
     *
     * @Serializer\Groups(
     *  {
     *      "profile",
     *      "profile_stranger",
     *      "profile_basic_stranger"
     *  }
     * )
     */
    private $experiences;

    /**
     * @var array
     *
     * @Serializer\Groups(
     *  {
     *      "profile",
     *      "profile_stranger",
     *      "profile_basic_stranger"
     *  }
     * )
     */
    private $educations;

    /**
     * @var array
     *
     * @Serializer\Groups(
     *  {
     *      "profile",
     *      "profile_stranger",
     *      "profile_basic_stranger"
     *  }
     * )
     */
    private $portfolios;

    /**
     * @var string
     *
     * @Serializer\Groups(
     * {
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy"
     *  }
     * )
     */
    private $status;

    /**
     * @var string
     *
     * @Serializer\Groups(
     *  {
     *      "profile",
     *      "profile_basic",
     *      "buddy"
     *  }
     * )
     */
    private $jid;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return UserProfile
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
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
     *
     * @return UserProfile
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     *
     * @return UserProfile
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     *
     * @return UserProfile
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param string $dateOfBirth
     *
     * @return UserProfile
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return UserProfile
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return UserProfile
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getAboutMe()
    {
        return $this->aboutMe;
    }

    /**
     * @param string $aboutMe
     *
     * @return UserProfile
     */
    public function setAboutMe($aboutMe)
    {
        $this->aboutMe = $aboutMe;
    }

    /**
     * @return string
     */
    public function getSkill()
    {
        return $this->skill;
    }

    /**
     * @param string $skill
     *
     * @return UserProfile
     */
    public function setSkill($skill)
    {
        $this->skill = $skill;
    }

    /**
     * @return string
     */
    public function getSinaWeibo()
    {
        return $this->sinaWeibo;
    }

    /**
     * @param string $sinaWeibo
     *
     * @return UserProfile
     */
    public function setSinaWeibo($sinaWeibo)
    {
        $this->sinaWeibo = $sinaWeibo;
    }

    /**
     * @return string
     */
    public function getTencentWeibo()
    {
        return $this->tencentWeibo;
    }

    /**
     * @param string $tencentWeibo
     *
     * @return UserProfile
     */
    public function setTencentWeibo($tencentWeibo)
    {
        $this->tencentWeibo = $tencentWeibo;
    }

    /**
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param string $facebook
     *
     * @return UserProfile
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * @return string
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * @param string $linkedin
     *
     * @return UserProfile
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;
    }

    /**
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param int $buildingId
     *
     * @return UserProfile
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
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
     *
     * @return UserProfile
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTime $modificationDate
     *
     * @return UserProfile
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return UserProfile
     */
    public function setUser($user)
    {
        $this->user = $user;
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
     *
     * @return UserProfile
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return array
     */
    public function getHobbyIds()
    {
        return $this->hobbyIds;
    }

    /**
     * @param array $hobbyIds
     *
     * @return UserProfile
     */
    public function setHobbyIds($hobbyIds)
    {
        $this->hobbyIds = $hobbyIds;
    }

    /**
     * @return array
     */
    public function getHobbies()
    {
        return $this->hobbies;
    }

    /**
     * @param array $hobbies
     *
     * @return UserProfile
     */
    public function setHobbies($hobbies)
    {
        $this->hobbies = $hobbies;
    }

    /**
     * @return mixed
     */
    public function getExperiences()
    {
        return $this->experiences;
    }

    /**
     * @param mixed $experiences
     *
     * @return UserProfile
     */
    public function setExperiences($experiences)
    {
        $this->experiences = $experiences;
    }

    /**
     * @return mixed
     */
    public function getEducations()
    {
        return $this->educations;
    }

    /**
     * @param mixed $educations
     *
     * @return UserProfile
     */
    public function setEducations($educations)
    {
        $this->educations = $educations;
    }

    /**
     * @return mixed
     */
    public function getPortfolios()
    {
        return $this->portfolios;
    }

    /**
     * @param mixed $portfolios
     *
     * @return UserProfile
     */
    public function setPortfolios($portfolios)
    {
        $this->portfolios = $portfolios;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return UserProfile
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set jid.
     *
     * @param string $jid
     *
     * @return UserProfile
     */
    public function setJid($jid)
    {
        $this->jid = $jid;

        return $this;
    }

    /**
     * Get jid.
     *
     * @return string
     */
    public function getJid()
    {
        return $this->jid;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
