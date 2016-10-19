<?php

namespace Sandbox\ApiBundle\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feature.
 *
 * @ORM\Table(name="features")
 * @ORM\Entity
 */
class Feature
{
    const FEATURE_FOOD = 'food';
    const FEATURE_COFFEE = 'coffee';
    const FEATURE_FORWARD = 'forward';

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
     * @ORM\Column(name="app", type="string", length=64)
     */
    private $app;

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
     * Set type.
     *
     * @param string $type
     *
     * @return Feature
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
     * @return Feature
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

    /**
     * @return string
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param string $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }
}
