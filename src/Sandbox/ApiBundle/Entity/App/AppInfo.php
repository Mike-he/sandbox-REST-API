<?php

namespace Sandbox\ApiBundle\Entity\App;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * AppInfo.
 *
 * @ORM\Table(
 *      name="AppInfo"
 * )
 * @ORM\Entity
 */
class AppInfo
{
    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main"})
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=16, nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=128, nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="date", type="string", length=16, nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $date;

    /**
     * Set platform.
     *
     * @param string $platform
     *
     * @return AppInfo
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
     * @return AppInfo
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
     * Set url.
     *
     * @param string $url
     *
     * @return AppInfo
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
     * Set date.
     *
     * @param string $date
     *
     * @return AppInfo
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }
}
