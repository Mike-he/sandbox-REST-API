<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sandbox\ApiBundle\Entity\Order\InvitedPeople;

/**
 * RoomUsageView.
 *
 * @ORM\Table(name="room_usage_view")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Room\RoomUsageViewRepository")
 */
class RoomUsageView
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
     * @var int
     *
     * @ORM\Column(name="productId", type="integer")
     */
    private $productId;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     *
     * @Serializer\Groups({"main", "room_usage"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     *
     * @Serializer\Groups({"main", "room_usage"})
     */
    private $endDate;

    /**
     * @var \Sandbox\ApiBundle\Entity\User\UserView
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\UserView")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "room_usage"})
     */
    private $user;

    /**
     * @var \Sandbox\ApiBundle\Entity\User\UserView
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\UserView")
     * @ORM\JoinColumn(name="appointedUser", referencedColumnName="id")
     *
     * @Serializer\Groups({"main", "room_usage"})
     */
    private $appointedUser;

    /**
     * @var InvitedPeople
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Order\InvitedPeople",
     *      mappedBy="orderId"
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="orderId")
     *
     * @Serializer\Groups({"main", "room_usage"})
     */
    private $invitedPeople;

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
     * Set productId.
     *
     * @param int $productId
     *
     * @return RoomUsageView
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId.
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return RoomUsageView
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime $startDate
     *
     * @return RoomUsageView
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime $endDate
     *
     * @return RoomUsageView
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set user.
     *
     * @param int $user
     *
     * @return RoomUsageView
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set appointedUser.
     *
     * @param int $appointedUser
     *
     * @return RoomUsageView
     */
    public function setAppointedUser($appointedUser)
    {
        $this->appointedUser = $appointedUser;

        return $this;
    }

    /**
     * Get appointedUser.
     *
     * @return int
     */
    public function getAppointedUser()
    {
        return $this->appointedUser;
    }

    /**
     * Set invitedPeople.
     *
     * @param array $invitedPeople
     *
     * @return RoomUsageView
     */
    public function setInvitedPeople($invitedPeople)
    {
        $this->invitedPeople = $invitedPeople;

        return $this;
    }

    /**
     * Get invitedPeople.
     *
     * @return array
     */
    public function getInvitedPeople()
    {
        return $this->invitedPeople;
    }
}
