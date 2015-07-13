<?php

namespace Sandbox\ApiBundle\Entity\Member;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client Member Recommend Random Record.
 *
 * @ORM\Table(name="ClientMemberRecommendRandomRecord")
 * @ORM\Entity
 */
class ClientMemberRecommendRandomRecord
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer",  nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer",  nullable=false)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="memberId", type="integer",  nullable=false)
     */
    private $memberId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return ClientMemberRecommendRandomRecord
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get memberId.
     *
     * @return int
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * Set memberId.
     *
     * @param int $memberId
     *
     * @return ClientMemberRecommendRandomRecord
     */
    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;
    }
}
