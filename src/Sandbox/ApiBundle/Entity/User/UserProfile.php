<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\Company\Company;

/**
 * User Profile.
 *
 * @ORM\Table(
 *      name="user_profiles",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="userId_UNIQUE", columns={"userId"})
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserProfileRepository"
 * )
 */
class UserProfile
{
    const GENDER_OTHER = 'other';
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

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
     *      "admin_order",
     *      "feed",
     *      "company_member_basic",
     *      "company_info",
     *      "chatgroup",
     *      "verify",
     *      "client_event",
     *      "admin_event"
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
     *      "admin_order",
     *      "feed",
     *      "company_member_basic",
     *      "company_info",
     *      "client",
     *      "chatgroup",
     *      "verify",
     *      "client_event",
     *      "admin_event",
     *      "client_evaluation",
     *      "admin_view"
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
     *      "member",
     *      "feed",
     *      "client_event",
     *      "admin_event"
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
    private $gender = self::GENDER_OTHER;

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
     *      "admin_order",
     *      "client"
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
     *      "admin_order",
     *      "client"
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
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $companyId;

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
     * @ORM\OneToOne(targetEntity="User", inversedBy="userProfile")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_basic",
     *      "profile_stranger",
     *      "profile_basic_stranger",
     *      "buddy",
     *      "member",
     *      "feed",
     *      "client_event",
     *      "admin_event"
     *  }
     * )
     **/
    private $building;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Company\Company")
     * @ORM\JoinColumn(name="companyId", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups(
     *  {
     *      "main",
     *      "profile",
     *      "profile_stranger",
     *      "profile_basic",
     *      "profile_basic_stranger",
     *      "member"
     *  }
     * )
     */
    private $company;

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
     *      "buddy",
     *      "member"
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
     *      "buddy",
     *      "chatgroup"
     *  }
     * )
     */
    private $jid;

    /**
     * @var int
     *
     * @Serializer\Groups(
     *  {
     *      "profile",
     *      "profile_basic",
     *      "buddy"
     *  }
     * )
     */
    private $buddyId;

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
     * Get companyId.
     *
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return UserProfile
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
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
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param Company $company
     *
     * @return UserProfile
     */
    public function setCompany($company)
    {
        $this->company = $company;
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

    /**
     * Set buddyId.
     *
     * @param int $buddyId
     *
     * @return UserProfile
     */
    public function setBuddyId($buddyId)
    {
        $this->buddyId = $buddyId;

        return $this;
    }

    /**
     * Get buddyId.
     *
     * @return int
     */
    public function getBuddyId()
    {
        return $this->buddyId;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
