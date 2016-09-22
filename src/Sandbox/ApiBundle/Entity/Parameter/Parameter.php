<?php

namespace Sandbox\ApiBundle\Entity\Parameter;

use Doctrine\ORM\Mapping as ORM;

/**
 * Parameter.
 *
 * @ORM\Table(
 *      name="parameter",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})}
 * )
 * @ORM\Entity
 */
class Parameter
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
     * @ORM\Column(name="key", type="string", length=64)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=128)
     */
    private $value;

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
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
