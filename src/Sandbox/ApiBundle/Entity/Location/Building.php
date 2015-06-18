<?php

namespace Sandbox\ApiBundle\Entity\Location;

use Doctrine\ORM\Mapping as ORM;

/**
 * Building
 *
 * @ORM\Table(name="Building")
 * @ORM\Entity
 */
class Building
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \Sandbox\ApiBundle\Entity\Location\City
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Location\City")
     * @ORM\JoinColumn(name="cityId", referencedColumnName="id")
     * @ORM\Column(name="cityId", type="integer")
     */
    private $cityId;

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
     * Set name
     *
     * @param  string   $name
     * @return Building
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set cityId
     *
     * @param  integer  $cityId
     * @return Building
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId
     *
     * @return integer
     */
    public function getCityId()
    {
        return $this->cityId;
    }
}
