<?php

namespace Sandbox\ApiBundle\Entity\ChatGroup;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Entity\User\User;
use JMS\Serializer\Annotation as Serializer;

/**
 * ChatGroupMember.
 *
 * @ORM\Table(
 *      name="chat_group_member",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="chatGroupId_userId_UNIQUE", columns={"chatGroupId", "userId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_ChatGroupMember_userId_idx", columns={"userId"}),
 *          @ORM\Index(name="fk_ChatGroupMember_addBy_idx", columns={"addBy"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\ChatGroup\ChatGroupMemberRepository"
 * )
 */
class ChatGroupMember
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main"})
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="chatGroupId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $chatGroup;

    /**
     * @var User
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $user;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="addBy", referencedColumnName="id")
     * })
     */
    private $addBy;

    /**
     * @var bool
     *
     * @ORM\Column(name="mute", type="boolean")
     *
     * @Serializer\Groups({"main"})
     */
    private $mute = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     */
    private $modificationDate;

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
     * @return ChatGroup
     */
    public function getChatGroup()
    {
        return $this->chatGroup;
    }

    /**
     * @param ChatGroup $chatGroup
     */
    public function setChatGroup($chatGroup)
    {
        $this->chatGroup = $chatGroup;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getAddBy()
    {
        return $this->addBy;
    }

    /**
     * @param User $addBy
     */
    public function setAddBy($addBy)
    {
        $this->addBy = $addBy;
    }

    /**
     * @return bool
     */
    public function isMute()
    {
        return $this->mute;
    }

    /**
     * @param bool $mute
     */
    public function setMute($mute)
    {
        $this->mute = $mute;
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ChatGroupMember
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return ChatGroupMember
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
