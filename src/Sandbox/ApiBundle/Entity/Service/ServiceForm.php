<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ServiceForm.
 *
 * @ORM\Table(name = "service_form")
 * @ORM\Entity
 */
class ServiceForm
{
    const TYPE_TEXT = 'text';
    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="service_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({
     *      "main"
     * })
     */
    private $serviceId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Service\Service
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Service\Service")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({
     *      "main"
     * })
     */
    private $service;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $type;

    /**
     * @var ServiceFormOption
     *
     * @ORM\OneToMany(targetEntity="Sandbox\ApiBundle\Entity\Service\ServiceFormOption",
     *      mappedBy="form",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="form_id")
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $options;

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
     * Set serviceId.
     *
     * @param int $serviceId
     *
     * @return serviceForm
     */
    public function setServicetId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get serviceId.
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set service.
     *
     * @param $service
     *
     * @return ServiceForm-
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service.
     *
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return ServiceForm
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return ServiceForm
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
     * Set options.
     *
     * @param ServiceFormOption $options
     *
     * @return ServiceForm
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options.
     *
     * @return ServiceFormOption
     */
    public function getOptions()
    {
        return $this->options;
    }
}
