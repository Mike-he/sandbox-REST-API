<?php

namespace Sandbox\ApiBundle\Entity\Random;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client Random Record.
 *
 * @ORM\Table(name="ClientRandomRecord")
 * @ORM\Entity
 */
class ClientRandomRecord
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="clientId", type="integer", nullable=true)
     */
    private $clientId;

    /**
     * @var int
     *
     * @ORM\Column(name="entityId", type="integer", nullable=true)
     */
    private $entityId;

    /**
     * @var string
     *
     * @ORM\Column(name="entityName", type="string", length=16, nullable=true)
     */
    private $entityName;

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
     * @return ClientRandomRecord
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get clientId.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientId.
     *
     * @param int $clientId
     *
     * @return ClientRandomRecord
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }
}
