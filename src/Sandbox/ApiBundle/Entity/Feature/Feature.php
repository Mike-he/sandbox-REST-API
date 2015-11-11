<?php

namespace Sandbox\ApiBundle\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feature.
 *
 * @ORM\Table(name="Feature")
 * @ORM\Entity
 */
class Feature
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
     * @ORM\Column(name="name", type="string", length=16)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="ready", type="boolean")
     */
    private $ready = false;

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
     * @return Feature
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
     * Set ready.
     *
     * @param bool $ready
     *
     * @return Feature
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
    public function getReady()
    {
        return $this->ready;
    }
}
