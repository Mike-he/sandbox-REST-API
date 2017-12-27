<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ServiceAttachment.
 *
 * @ORM\Table(name = "service_attachment")
 * @ORM\Entity
 */
class ServiceAttachment
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
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment_type", type="string", length=64, nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text", nullable=true)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $preview;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="integer", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "client_service",
     *      "admin_service"
     * })
     */
    private $size;

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
     * @return ServiceAttachment
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
     * @return ServiceAttachment
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
    public function getservice()
    {
        return $this->service;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return ServiceAttachment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set attachmentType.
     *
     * @param string $attachmentType
     *
     * @return ServiceAttachment
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentType = $attachmentType;

        return $this;
    }

    /**
     * Get attachmentType.
     *
     * @return string
     */
    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     *
     * @return ServiceAttachment
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set preview.
     *
     * @param string $preview
     *
     * @return ServiceAttachment
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Get preview.
     *
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set size.
     *
     * @param string $size
     *
     * @return ServiceAttachment
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }
}
