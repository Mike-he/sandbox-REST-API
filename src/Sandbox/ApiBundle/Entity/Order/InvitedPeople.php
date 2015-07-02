<?php

namespace Sandbox\ApiBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;

/**
 * InvitedPeople.
 *
 * @ORM\Table(name="InvitedPeople")
 * @ORM\Entity
 */
class InvitedPeople
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
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Order\ProductOrder", inversedBy="invitedPeople")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="orderId", referencedColumnName="id")
     * })
     */
    private $orderId;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

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
     * Set orderId.
     *
     * @param int $orderId
     *
     * @return InvitedPeople
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get orderId.
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return InvitedPeople
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
}
