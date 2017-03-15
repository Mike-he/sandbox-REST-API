<?php

namespace Sandbox\ApiBundle\Entity\Menu;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuBar.
 *
 * @ORM\Table(name="menu")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Menu\MenuRepository")
 */
class Menu
{
    const COMPONENT_CLIENT = 'client';
    const COMPONENT_ADMIN = 'admin';

    const POSITION_MAIN = 'main';
    const POSITION_PROFILE = 'profile';
    const POSITION_HOME = 'home';

    const PLATFORM_IPHONE = 'iphone';
    const PLATFORM_ANDROID = 'android';

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
     * @ORM\Column(name="minVersion", type="string", length=16)
     */
    private $minVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="maxVersion", type="string", length=16)
     */
    private $maxVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="mainJson", type="text")
     */
    private $mainJson;

    /**
     * @var string
     *
     * @ORM\Column(name="profileJson", type="text")
     */
    private $profileJson;

    /**
     * @var string
     *
     * @ORM\Column(name="homeJson", type="text")
     */
    private $homeJson;

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
     * @return string
     */
    public function getMinVersion()
    {
        return $this->minVersion;
    }

    /**
     * @param string $minVersion
     */
    public function setMinVersion($minVersion)
    {
        $this->minVersion = $minVersion;
    }

    /**
     * @return string
     */
    public function getMaxVersion()
    {
        return $this->maxVersion;
    }

    /**
     * @param string $maxVersion
     */
    public function setMaxVersion($maxVersion)
    {
        $this->maxVersion = $maxVersion;
    }

    /**
     * @return string
     */
    public function getMainJson()
    {
        return $this->mainJson;
    }

    /**
     * @param string $mainJson
     */
    public function setMainJson($mainJson)
    {
        $this->mainJson = $mainJson;
    }

    /**
     * @return string
     */
    public function getProfileJson()
    {
        return $this->profileJson;
    }

    /**
     * @param string $profileJson
     */
    public function setProfileJson($profileJson)
    {
        $this->profileJson = $profileJson;
    }

    /**
     * @return string
     */
    public function getHomeJson()
    {
        return $this->homeJson;
    }

    /**
     * @param string $homeJson
     */
    public function setHomeJson($homeJson)
    {
        $this->homeJson = $homeJson;
    }
}
