<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * FinanceBillAttachment.
 *
 * @ORM\Table(name = "finance_bill_attachment")
 * @ORM\Entity
 */
class FinanceBillAttachment
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
     * @var \Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Finance\FinanceLongRentBill")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bill_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main"})
     */
    private $bill;

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
     * @ORM\Column(name="attachment_type", type="string", length=255, nullable=true)
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
     * @return FinanceLongRentBill
     */
    public function getBill()
    {
        return $this->bill;
    }

    /**
     * @param FinanceLongRentBill $bill
     */
    public function setBill($bill)
    {
        $this->bill = $bill;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    /**
     * @param string $attachmentType
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentType = $attachmentType;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param string $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
}
