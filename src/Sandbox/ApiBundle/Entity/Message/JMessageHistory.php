<?php

namespace Sandbox\ApiBundle\Entity\Message;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Message.
 *
 * @ORM\Table(name="jmessages_history")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Message\MessageRepository")
 */
class JMessageHistory
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
     * @var string
     *
     * @ORM\Column(name="msg_body", type="text")
     */
    private $msgBody;

    /**
     * @var string
     *
     * @ORM\Column(name="from_id", type="string",length=25)
     */
    private $fromId;

    /**
     * @var string
     *
     * @ORM\Column(name="target_id", type="string",length=25)
     */
    private $targetId;

    /**
     * @var string
     *
     * @ORM\Column(name="msg_ctime", type="string",length=25)
     */
    private $msgCtime;

    /**
     * @var string
     *
     * @ORM\Column(name="target_type", type="string",length=25)
     */
    private $targetType;

    /**
     * @var string
     *
     * @ORM\Column(name="from_app_key", type="string",length=64)
     */
    private $fromAppKey;

    /**
     * @var string
     *
     * @ORM\Column(name="msg_id", type="string",length=64)
     */
    private $msgId;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

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
     * @return string
     */
    public function getMsgBody()
    {
        return $this->msgBody;
    }

    /**
     * @param string $msgBody
     */
    public function setMsgBody($msgBody)
    {
        $this->msgBody = $msgBody;
    }

    /**
     * @return string
     */
    public function getFromId()
    {
        return $this->fromId;
    }

    /**
     * @param string $fromId
     */
    public function setFromId($fromId)
    {
        $this->fromId = $fromId;
    }

    /**
     * @return string
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @param string $targetId
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }

    /**
     * @return string
     */
    public function getMsgCtime()
    {
        return $this->msgCtime;
    }

    /**
     * @param string $msgCtime
     */
    public function setMsgCtime($msgCtime)
    {
        $this->msgCtime = $msgCtime;
    }

    /**
     * @return string
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * @param string $targetType
     */
    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;
    }

    /**
     * @return string
     */
    public function getFromAppKey()
    {
        return $this->fromAppKey;
    }

    /**
     * @param string $fromAppKey
     */
    public function setFromAppKey($fromAppKey)
    {
        $this->fromAppKey = $fromAppKey;
    }

    /**
     * @return string
     */
    public function getMsgId()
    {
        return $this->msgId;
    }

    /**
     * @param string $msgId
     */
    public function setMsgId($msgId)
    {
        $this->msgId = $msgId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Message
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
}
