<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * TransferAttachment.
 *
 * @ORM\Table(name = "TransferAttachment")
 * @ORM\Entity
 */
class TransferAttachment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="transferId", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $transferId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Order\OrderOfflineTransfer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transferId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $transfer;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="attachmentType", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $attachmentType;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text", nullable=true)
     *
     * @Serializer\Groups({"main"})
     */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main"})
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
     * Set transferId.
     *
     * @param int $transferId
     *
     * @return TransferAttachment
     */
    public function setTransferId($transferId)
    {
        $this->transferId = $transferId;

        return $this;
    }

    /**
     * Get transferId.
     *
     * @return int
     */
    public function getTransferId()
    {
        return $this->transferId;
    }

    /**
     * Set transfer.
     *
     * @param $transfer
     *
     * @return TransferAttachment
     */
    public function setTransfer($transfer)
    {
        $this->transfer = $transfer;

        return $this;
    }

    /**
     * Get transfer.
     *
     * @return OrderOfflineTransfer
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return TransferAttachment
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
     * @return TransferAttachment
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
     * @return TransferAttachment
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
     * @return TransferAttachment
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
     * @param int $size
     *
     * @return TransferAttachment
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
