<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use JMS\Serializer\Annotation as Serializer;

/**
 * User view.
 *
 * @ORM\Table(name="UserView")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserRepository"
 * )
 */
class UserView
{
    const DEFAULT_GENDER_OTHER = 'other';
    const DEFAULT_GENDER_MALE = 'male';
    const DEFAULT_GENDER_FEMALE = 'female';
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "login"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "login"})
     */
    private $phone;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $banned = false;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $gender = self::DEFAULT_GENDER_OTHER;

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
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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
     * Is banned.
     *
     * @return bool
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }
}
