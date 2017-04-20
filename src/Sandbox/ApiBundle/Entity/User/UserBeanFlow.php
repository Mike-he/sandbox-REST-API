<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserBeanFlow.
 *
 * @ORM\Table(name="user_bean_flows")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\User\UserBeanFlowRepository")
 */
class UserBeanFlow
{
    const TYPE_ADD = 'add'; //余额
    const TYPE_CONSUME = 'consume'; //消费

    const SOURCE_EXCHANGE = 'exchange';
    const SOURCE_EXCHANGE_FAIL = 'exchange_fail';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="change_amount", type="float")
     */
    private $changeAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="balance", type="float")
     */
    private $balance;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=50)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="trade_id", type="string", length=50, nullable=true)
     */
    private $tradeId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
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

    /**
     * @return string
     */
    public function getChangeAmount()
    {
        return $this->changeAmount;
    }

    /**
     * @param string $changeAmount
     */
    public function setChangeAmount($changeAmount)
    {
        $this->changeAmount = $changeAmount;
    }

    /**
     * @return string
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param string $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getTradeId()
    {
        return $this->tradeId;
    }

    /**
     * @param string $tradeId
     */
    public function setTradeId($tradeId)
    {
        $this->tradeId = $tradeId;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
