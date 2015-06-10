<?php
/**
 * UserProfile entity
 *
 * PHP version 5.3
 *
 * @category Sandbox
 * @package  ApiBundle
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 */
namespace Sandbox\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VCard
 *
 * @ORM\Table(name="UserProfile")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Entity\JtVCardRepository"
 * )
 *
 */
class UserProfile
{
    const DEFAULT_GENDER_OTHER = 'other';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer",  nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="userId", type="string", nullable=false)
     */
    private $userid;

    /**
     * @var int
     *
     * @ORM\Column(name="companyId", type="integer", nullable=false)
     */
    private $companyid;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=128, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=false)
     */
    private $gender = self::DEFAULT_GENDER_OTHER;

    /**
     * @var string
     *
     * @ORM\Column(name="aboutme", type="string", nullable=true)
     */
    private $aboutme;

    /**
     * @var string
     *
     * @ORM\Column(name="hobbies", type="string", nullable=true)
     */
    private $hobbies;

    /**
     * @var string
     *
     * @ORM\Column(name="skills", type="string", nullable=true)
     */
    private $skills;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=512, nullable=true)
     */
    private $location;

    /**
     * Set companyid
     *
     * @param  string  $companyid
     * @return JtVCard
     */
    public function setCompanyid($companyid)
    {
        $this->companyid = $companyid;

        return $this;
    }

    /**
     * Get companyid
     * @return string
     */
    public function getCompanyid()
    {
        return $this->companyid;
    }

    /**
     * Set userid
     *
     * @param  string  $userid
     * @return JtVCard
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     * @return string
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return JtVCard
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param  string  $email
     * @return JtVCard
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param  string  $phone
     * @return JtVCard
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set gender
     *
     * @param  string  $gender
     * @return JtVCard
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set aboutme
     *
     * @param  string  $aboutme
     * @return JtVCard
     */
    public function setAboutme($aboutme)
    {
        $this->aboutme = $aboutme;

        return $this;
    }

    /**
     * Get aboutme
     * @return string
     */
    public function getAboutme()
    {
        return $this->aboutme;
    }

    /**
     * Set hobbies
     *
     * @param  string  $hobbies
     * @return JtVCard
     */
    public function setHobbies($hobbies)
    {
        $this->hobbies = $hobbies;

        return $this;
    }

    /**
     * Get hobbies
     * @return string
     */
    public function getHobbies()
    {
        return $this->hobbies;
    }

    /**
     * Set skills
     *
     * @param  string  $skills
     * @return JtVCard
     */
    public function setSkills($skills)
    {
        $this->skills = $skills;

        return $this;
    }

    /**
     * Get skills
     * @return string
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * Set loction
     *
     * @param  string  $location
     * @return JtVCard
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get vcardid
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
