<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ServiceType.
 *
 * @ORM\Table(name="service_types")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Service\ServiceTypeRepository")
 */
class ServiceTypes
{
    const TYPE_NAME_STRATING_BUSSINESS = 'starting_business';
    const TYPE_NAME_FINANCIAL_COLLECTION = 'financial_collection';
    const TYPE_NAME_LAGAL_ADVICE = 'legal_advice';
    const TYPE_NAME_OTHER = 'other';
    const TYPE_NAME_DATA_SERVICE = 'data_service';
    const TYPE_NAME_MEDIA_SERVICE = 'media_service';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({
     *     "main",
     *     "admin_service",
     *     "client_service"
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", length=255)
     */
    private $key;

    /**
     * @var int
     *
     * @ORM\Column(name="sort", type="integer", options={"default": 1})
     */
    private $sort = 1;

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
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

}
