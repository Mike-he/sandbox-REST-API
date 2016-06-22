<?php

namespace Sandbox\ApiBundle\Entity\Menu;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuBar.
 *
 * @ORM\Table(name="Menu")
 * @ORM\Entity
 */
class Menu
{
    const COMPONENT_CLIENT = 'client';
    const COMPONENT_ADMIN = 'admin';

    const POSITION_LEFT = 'left';
    const POSITION_RIGHT = 'right';

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
     * @ORM\Column(name="component", type="string", length=16)
     */
    private $component;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=16)
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=16)
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=16)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="menuJson", type="text")
     */
    private $menuJson;

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
     * Set component.
     *
     * @param string $component
     *
     * @return Menu
     */
    public function setComponent($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component.
     *
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set platform.
     *
     * @param string $platform
     *
     * @return Menu
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform.
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set version.
     *
     * @param string $version
     *
     * @return Menu
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Set menuJson.
     *
     * @param string $menuJson
     *
     * @return Menu
     */
    public function setMenuJson($menuJson)
    {
        $this->menuJson = $menuJson;

        return $this;
    }

    /**
     * Get menuJson.
     *
     * @return string
     */
    public function getMenuJson()
    {
        return $this->menuJson;
    }
}
