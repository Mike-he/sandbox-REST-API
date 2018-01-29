<?php

namespace Sandbox\ApiBundle\Entity\Banner;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * BannerTag.
 *
 * @ORM\Table(name="banner_tag")
 * @ORM\Entity
 */
class BannerTag
{
    const ADVERTISEMENT = 'banner.tag.advertisement';
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=64)
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $key;

    /**
     * @var string
     *
     * @Serializer\Groups({"main", "client_list"})
     */
    private $name;

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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return BannerTag
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
}
