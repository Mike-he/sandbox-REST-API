<?php

namespace Sandbox\ApiBundle\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Evaluation.
 *
 * @ORM\Table(name = "evaluation")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Evaluation\EvaluationRepository")
 */
class Evaluation
{
    const TYPE_OFFICIAL = 'official';
    const TYPE_BUILDING = 'building';
    const TYPE_ORDER = 'order';

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
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var float
     *
     * @ORM\Column(name="totalStar", type="float")
     */
    private $totalStar;

    /**
     * @var float
     *
     * @ORM\Column(name="serviceStar", type="float")
     */
    private $serviceStar;

    /**
     * @var float
     *
     * @ORM\Column(name="environmentStar", type="float")
     */
    private $environmentStar;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer")
     */
    private $buildingId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $building;

    /**
     * @var int
     *
     * @ORM\Column(name="productOrderId", type="integer",nullable=true)
     */
    private $productOrderId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Order\ProductOrder")
     * @ORM\JoinColumn(name="productOrderId", referencedColumnName="id", onDelete="SET NULL")
     */
    private $productOrder;

    /**
     * @var array
     */
    private $attachments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     *
     */
    private $visible = true;

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
     * Set type.
     *
     * @param string $type
     *
     * @return Evaluation
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
     * Set totalStar.
     *
     * @param float $totalStar
     *
     * @return Evaluation
     */
    public function setTotalStar($totalStar)
    {
        $this->totalStar = $totalStar;

        return $this;
    }

    /**
     * Get totalStar.
     *
     * @return float
     */
    public function getTotalStar()
    {
        return $this->totalStar;
    }

    /**
     * Set serviceStar.
     *
     * @param float $serviceStar
     *
     * @return Evaluation
     */
    public function setServiceStar($serviceStar)
    {
        $this->serviceStar = $serviceStar;

        return $this;
    }

    /**
     * Get serviceStar.
     *
     * @return float
     */
    public function getServiceStar()
    {
        return $this->serviceStar;
    }

    /**
     * Set environmentStar.
     *
     * @param float $environmentStar
     *
     * @return Evaluation
     */
    public function setEnvironmentStar($environmentStar)
    {
        $this->environmentStar = $environmentStar;

        return $this;
    }

    /**
     * Get environmentStar.
     *
     * @return float
     */
    public function getEnvironmentStar()
    {
        return $this->environmentStar;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return Evaluation
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return Evaluation
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set buildingId.
     *
     * @param int $buildingId
     *
     * @return Evaluation
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId.
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set productOrderId.
     *
     * @param int $productOrderId
     *
     * @return Evaluation
     */
    public function setProductOrderId($productOrderId)
    {
        $this->productOrderId = $productOrderId;

        return $this;
    }

    /**
     * Get productOrderId.
     *
     * @return int
     */
    public function getProductOrderId()
    {
        return $this->productOrderId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Evaluation
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set user.
     *
     * @param \Sandbox\ApiBundle\Entity\User\User $user
     *
     * @return Evaluation
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Sandbox\ApiBundle\Entity\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set building.
     *
     * @param \Sandbox\ApiBundle\Entity\Room\RoomBuilding $building
     *
     * @return Evaluation
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get building.
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Set productOrder.
     *
     * @param \Sandbox\ApiBundle\Entity\Order\ProductOrder $productOrder
     *
     * @return Evaluation
     */
    public function setProductOrder($productOrder)
    {
        $this->productOrder = $productOrder;

        return $this;
    }

    /**
     * Get productOrder.
     *
     * @return \Sandbox\ApiBundle\Entity\Order\ProductOrder
     */
    public function getProductOrder()
    {
        return $this->productOrder;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param array $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param boolean $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }
}
