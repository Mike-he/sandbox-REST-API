<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ServiceTime.
 *
 * @ORM\Table(name = "service_times")
 * @ORM\Entity
 */
class ServiceTime
{
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
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="time", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="time", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $endTime;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
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
     * Set serviceId.
     *
     * @param $serviceId
     *
     * @return ServiceTime
     */
    public function setServiceId($serviceId)
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
     * @return ServiceTime
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
     * Set startTime.
     *
     * @param \DateTime $startTime
     *
     * @return ServiceTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime.
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime.
     *
     * @param \DateTime $endTime
     *
     * @return ServiceTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime.
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return ServiceTime
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
