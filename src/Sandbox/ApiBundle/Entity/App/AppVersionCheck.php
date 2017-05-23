<?php

namespace Sandbox\ApiBundle\Entity\App;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AppVersionCheck
 *
 * @ORM\Table(name="app_version_check")
 * @ORM\Entity
 */
class AppVersionCheck
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="current_version", type="string", length=64)
     */
    private $currentVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="zh_notification", type="string", length=1024)
     */
    private $zhNotification;

    /**
     * @var string
     *
     * @ORM\Column(name="zh_force_notification", type="string", length=1024)
     */
    private $zhForceNotification;

    /**
     * @var string
     *
     * @ORM\Column(name="en_notification", type="string", length=1024)
     */
    private $enNotification;

    /**
     * @var string
     *
     * @ORM\Column(name="en_force_notification", type="string", length=1024)
     */
    private $enForceNotification;

    /**
     * @var string
     *
     * @ORM\Column(name="ios_url", type="string", length=255)
     */
    private $ios_url;

    /**
     * @var string
     *
     * @ORM\Column(name="android_url", type="string", length=255)
     */
    private $androidUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_force", type="boolean")
     */
    private $isForce;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime")
     */
    private $modificationDate;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set currentVersion
     *
     * @param string $currentVersion
     * @return AppVersionCheck
     */
    public function setCurrentVersion($currentVersion)
    {
        $this->currentVersion = $currentVersion;

        return $this;
    }

    /**
     * Get currentVersion
     *
     * @return string 
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * Set zhNotification
     *
     * @param string $zhNotification
     * @return AppVersionCheck
     */
    public function setZhNotification($zhNotification)
    {
        $this->zhNotification = $zhNotification;

        return $this;
    }

    /**
     * Get zhNotification
     *
     * @return string 
     */
    public function getZhNotification()
    {
        return $this->zhNotification;
    }

    /**
     * Set enNotification
     *
     * @param string $enNotification
     * @return AppVersionCheck
     */
    public function setEnNotification($enNotification)
    {
        $this->enNotification = $enNotification;

        return $this;
    }

    /**
     * Get enNotification
     *
     * @return string 
     */
    public function getEnNotification()
    {
        return $this->enNotification;
    }

    /**
     * @return string
     */
    public function getZhForceNotification()
    {
        return $this->zhForceNotification;
    }

    /**
     * @param string $zhForceNotification
     */
    public function setZhForceNotification($zhForceNotification)
    {
        $this->zhForceNotification = $zhForceNotification;
    }

    /**
     * @return string
     */
    public function getEnForceNotification()
    {
        return $this->enForceNotification;
    }

    /**
     * @param string $enForceNotification
     */
    public function setEnForceNotification($enForceNotification)
    {
        $this->enForceNotification = $enForceNotification;
    }

    /**
     * Set isForce
     *
     * @param boolean $isForce
     * @return AppVersionCheck
     */
    public function setIsForce($isForce)
    {
        $this->isForce = $isForce;

        return $this;
    }

    /**
     * Get isForce
     *
     * @return boolean 
     */
    public function getIsForce()
    {
        return $this->isForce;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     * @return AppVersionCheck
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @return string
     */
    public function getIosUrl()
    {
        return $this->ios_url;
    }

    /**
     * @param string $ios_url
     */
    public function setIosUrl($ios_url)
    {
        $this->ios_url = $ios_url;
    }

    /**
     * @return string
     */
    public function getAndroidUrl()
    {
        return $this->androidUrl;
    }

    /**
     * @param string $androidUrl
     */
    public function setAndroidUrl($androidUrl)
    {
        $this->androidUrl = $androidUrl;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return AppVersionCheck
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     * @return AppVersionCheck
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime 
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }
}
