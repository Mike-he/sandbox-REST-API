<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * User Profile My Orders.
 *
 * @ORM\Table(name="UserProfileMyOrders")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\User\UserProfileMyOrdersRepository")
 */
class UserProfileMyOrders
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
     * @return UserProfileMyOrders
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
     * @return UserProfileMyOrders
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
     * @return UserProfileMyOrders
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
