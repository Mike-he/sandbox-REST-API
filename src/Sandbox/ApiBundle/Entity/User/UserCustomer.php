<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * UserInfo.
 *
 * @ORM\Table(name="user_customer")
 * @ORM\Entity
 */
class UserCustomer
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
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     */
    private $companyId;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @Assert\NotBlank();
     * @ORM\Column(name="phone_code", type="string", length=16, nullable=false)
     */
    private $phoneCode;

    /**
     * @var string
     *
     * @Assert\NotBlank();
     * @ORM\Column(name="phone", type="string", length=64, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sex", type="string", length=16, nullable=true)
     */
    private $sex;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=64, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="nationality", type="string", length=64, nullable=true)
     */
    private $nationality;

    /**
     * @var string
     *
     * @ORM\Column(name="id_type", type="string", length=64, nullable=true)
     */
    private $idType;

    /**
     * @var string
     *
     * @ORM\Column(name="id_number", type="string", length=64, nullable=true)
     */
    private $idNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=64, nullable=true)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="birthday", type="string", length=16, nullable=true)
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=64, nullable=true)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=64, nullable=true)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(name="modification_date", type="datetime")
     */
    private $modificationDate;

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
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Set companyId.
     *
     * @param int $companyId
     *
     * @return UserCustomer
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
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
     * Set name.
     *
     * @param string $name
     *
     * @return UserCustomer
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
     * Set sex.
     *
     * @param string $sex
     *
     * @return UserCustomer
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex.
     *
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return UserCustomer
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }

    /**
     * @param string $phoneCode
     */
    public function setPhoneCode($phoneCode)
    {
        $this->phoneCode = $phoneCode;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return UserCustomer
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set nationality.
     *
     * @param string $nationality
     *
     * @return UserCustomer
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Get nationality.
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Set idType.
     *
     * @param string $idType
     *
     * @return UserCustomer
     */
    public function setIdType($idType)
    {
        $this->idType = $idType;

        return $this;
    }

    /**
     * Get idType.
     *
     * @return string
     */
    public function getIdType()
    {
        return $this->idType;
    }

    /**
     * Set idNumber.
     *
     * @param string $idNumber
     *
     * @return UserCustomer
     */
    public function setIdNumber($idNumber)
    {
        $this->idNumber = $idNumber;

        return $this;
    }

    /**
     * Get idNumber.
     *
     * @return string
     */
    public function getIdNumber()
    {
        return $this->idNumber;
    }

    /**
     * Set language.
     *
     * @param string $language
     *
     * @return UserCustomer
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set birthday.
     *
     * @param string $birthday
     *
     * @return UserCustomer
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday.
     *
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set companyName.
     *
     * @param string $companyName
     *
     * @return UserCustomer
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set position.
     *
     * @param string $position
     *
     * @return UserCustomer
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return UserCustomer
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return UserCustomer
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
     * @return UserCustomer
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
}
