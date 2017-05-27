<?php

namespace Sandbox\ApiBundle\Entity\Property;

use Doctrine\ORM\Mapping as ORM;

/**
 * PropertyTypes.
 *
 * @ORM\Table(name="property_types")
 * @ORM\Entity
 */
class PropertyTypes
{
    const TRANS_PROPERTY_TYPE = 'property.type.';

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
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="application_icon", type="text")
     */
    private $applicationIcon;

    /**
     * @var string
     *
     * @ORM\Column(name="community_icon", type="text")
     */
    private $communityIcon;

    /**
     * @var string
     *
     * @ORM\Column(name="application_selected_icon", type="text")
     */
    private $applicationSelectedIcon;

    /**
     * @var string
     */
    private $description;

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
    public function getApplicationSelectedIcon()
    {
        return $this->applicationSelectedIcon;
    }

    /**
     * @param string $applicationSelectedIcon
     */
    public function setApplicationSelectedIcon($applicationSelectedIcon)
    {
        $this->applicationSelectedIcon = $applicationSelectedIcon;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return PropertyTypes
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
     * Set applicationIcon.
     *
     * @param string $applicationIcon
     *
     * @return PropertyTypes
     */
    public function setApplicationIcon($applicationIcon)
    {
        $this->applicationIcon = $applicationIcon;

        return $this;
    }

    /**
     * Get applicationIcon.
     *
     * @return string
     */
    public function getApplicationIcon()
    {
        return $this->applicationIcon;
    }

    /**
     * @return string
     */
    public function getCommunityIcon()
    {
        return $this->communityIcon;
    }

    /**
     * @param string $communityIcon
     */
    public function setCommunityIcon($communityIcon)
    {
        $this->communityIcon = $communityIcon;
    }
}
