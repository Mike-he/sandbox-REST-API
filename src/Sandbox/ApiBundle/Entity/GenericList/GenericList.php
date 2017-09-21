<?php

namespace Sandbox\ApiBundle\Entity\GenericList;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * GenericList.
 *
 * @ORM\Table(name="generic_list")
 * @ORM\Entity
 */
class GenericList
{
    const OBJECT_PLATFORM_OFFICIAL = 'official';
    const OBJECT_PLATFORM_SALES = 'sales';

    const OBJECT_LEASE = 'lease';
    const OBJECT_LEASE_BILL = 'lease_bill';
    const OBJECT_LEASE_CLUE = 'lease_clue';
    const OBJECT_LEASE_OFFER = 'lease_offer';
    const OBJECT_CUSTOMER = 'customer';
    const OBJECT_ENTERPRISE = 'enterprise';
    const OBJECT_CASHIER = 'cashier';
    const OBJECT_RESERVATION = 'reservation';
    const OBJECT_PRODUCT_ORDER = 'product_order';
    const OBJECT_EVENT_ORDER = 'event_order';
    const OBJECT_MEMBERSHIP_ORDER = 'membership_order';
    const OBJECT_CUSTOMER_INVOICE = 'customer_invoice';

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
     * @ORM\Column(name="platform", type="string", length=16)
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(name="object", type="string", length=16)
     */
    private $object;

    /**
     * @var string
     *
     * @ORM\Column(name="`column`", type="string", length=32)
     */
    private $column;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32)
     */
    private $name;

    /**
     * @var
     *
     * @ORM\Column(name="`default`", type="boolean")
     */
    private $default = false;

    /**
     * @var
     *
     * @ORM\Column(name="required", type="boolean")
     */
    private $required = false;

    /**
     * @var
     *
     * @ORM\Column(name="sort", type="boolean")
     */
    private $sort = false;

    /**
     * @var
     *
     * @ORM\Column(name="direction", type="string", length=16, nullable=true)
     */
    private $direction;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

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
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param string $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return mixed
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param mixed $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
}
