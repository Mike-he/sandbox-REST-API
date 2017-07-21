<?php

namespace Sandbox\ApiBundle\Entity\Lease;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="lease_rent_types")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Lease\LeaseRentTypesRepository")
 */
class LeaseRentTypes
{
    const RENT_TYPE_RENT = 'rent';
    const RENT_TYPE_TAX = 'tax';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"main", "lease_rent_types_list", "admin_room", "admin_appointment", "client"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     * @Serializer\Groups({"main", "lease_rent_types_list", "admin_room", "admin_appointment", "client"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_en", type="string", length=100)
     * @Serializer\Groups({"main", "lease_rent_types_list", "admin_room", "admin_appointment", "client"})
     */
    private $nameEn;

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = true;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=20)
     * @Serializer\Groups({"main", "lease_rent_types_list", "admin_room", "admin_appointment", "client"})
     */
    private $type;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return string
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * @param string $nameEn
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;
    }

    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
