<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * User view.
 *
 * @ORM\Table(name="UserView")
 * @ORM\Entity(
 *     repositoryClass="Sandbox\ApiBundle\Repository\User\UserViewRepository"
 * )
 */
class UserView
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "account", "client", "room_usage"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=true)
     * @Serializer\Groups({"main", "account", "client", "room_usage"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main", "account", "client", "room_usage"})
     */
    private $phone;

    /**
     * @var bool
     *
     * @ORM\Column(name="banned", type="boolean", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $banned;

    /**
     * @var bool
     *
     * @ORM\Column(name="authorized", type="boolean", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $authorized;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "account", "client", "room_usage"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $gender;

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
     * Is authorized.
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->authorized;
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
