<?php

namespace Sandbox\ApiBundle\Entity\ChatGroup;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Entity\User\User;
use JMS\Serializer\Annotation as Serializer;

/**
 * ChatGroup.
 *
 * @ORM\Table(name="chat_group")
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\ChatGroup\ChatGroupRepository"
 * )
 */
class ChatGroup
{
    const XMPP_SERVICE = 'conference';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "chatgroup"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Serializer\Groups({"main", "chatgroup"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="creatorId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "chatgroup"})
     */
    private $creatorId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creatorId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $creator;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @Serializer\Groups({"chatgroup"})
     */
    private $members;


    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer", nullable=false)
     */
    private $companyId;

    /**
     * @var int
     *
     * @ORM\Column(name="building_id", type="integer", nullable=false)
     */
    private $buildingId;

    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=16, options={"default": "service"})
     */
    private $tag = 'service';


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
     * Set name.
     *
     * @param string $name
     *
     * @return ChatGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $creatorId
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;
    }

    /**
     * @return int
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * @param User $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return ChatGroup
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return ChatGroup
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
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
     * @param array $members
     */
    public function setMembers($members)
    {
        $this->members = $members;
    }

    /**
     * @return array
     */
    public function getMembers()
    {
        return $this->members;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }

    /**
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param int $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @return mixed
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param mixed $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }
}
