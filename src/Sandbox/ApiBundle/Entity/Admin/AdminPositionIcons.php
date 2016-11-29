<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPositionIcons.
 *
 * @ORM\Table(name="admin_position_icons")
 * @ORM\Entity
 */
class AdminPositionIcons
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "admin"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=1024)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="selectedIcon", type="string", length=1024)
     */
    private $selectedIcon;

    /**
     * @var string
     * @Serializer\Groups({"main", "admin"})
     */
    private $url;

    /**
     * @var string
     * @Serializer\Groups({"main", "admin"})
     */
    private $selectedUrl;

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
     * Set icon.
     *
     * @param string $icon
     *
     * @return AdminPositionIcons
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getSelectedIcon()
    {
        return $this->selectedIcon;
    }

    /**
     * @param string $selectedIcon
     */
    public function setSelectedIcon($selectedIcon)
    {
        $this->selectedIcon = $selectedIcon;
    }

    /**
     * @return string
     */
    public function getSelectedUrl()
    {
        return $this->selectedUrl;
    }

    /**
     * @param string $selectedUrl
     */
    public function setSelectedUrl($selectedUrl)
    {
        $this->selectedUrl = $selectedUrl;
    }
}
