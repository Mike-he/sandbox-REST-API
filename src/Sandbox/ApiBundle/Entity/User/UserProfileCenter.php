<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * User Profile Center.
 *
 * @ORM\Table(name="UserProfileCenter")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\User\UserProfileCenterRepository")
 */
class UserProfileCenter
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
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="icons", type="text", nullable=true)
     */
    private $icons;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=2048, nullable=true)
     */
    private $url;

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
     * @return UserProfileCenter
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
     * Set icons.
     *
     * @param string $icons
     *
     * @return UserProfileCenter
     */
    public function setIcons($icons)
    {
        $this->icons = $icons;

        return $this;
    }

    /**
     * Get icons.
     *
     * @return string
     */
    public function getIcons()
    {
        return $this->icons;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return UserProfileCenter
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
