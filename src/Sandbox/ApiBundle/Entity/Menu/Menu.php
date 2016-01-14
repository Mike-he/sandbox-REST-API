<?php

namespace Sandbox\ApiBundle\Entity\Menu;

use Doctrine\ORM\Mapping as ORM;

/**
 * Menu.
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
     * @ORM\Column(name="key", type="string", length=16)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=16)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=512)
     */
    private $url;

    /**
     * @var bool
     *
     * @ORM\Column(name="ready", type="boolean")
     */
    private $ready = false;

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
     * @var int
     *
     * @ORM\Column(name="section", type="integer")
     */
    private $section;

    /**
     * @var int
     *
     * @ORM\Column(name="part", type="integer")
     */
    private $part;

    /**
     * @var int
     *
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

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
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * Set key.
     *
     * @param string $key
     *
     * @return Menu
     */
    public function setName($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Menu
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Menu
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

    /**
     * Set ready.
     *
     * @param bool $ready
     *
     * @return Menu
     */
    public function setReady($ready)
    {
        $this->ready = $ready;

        return $this;
    }

    /**
     * Get ready.
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->ready;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
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
     * @return int
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param int $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return int
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * @param int $part
     */
    public function setPart($part)
    {
        $this->part = $part;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }
}
