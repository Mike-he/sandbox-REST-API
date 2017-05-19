<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\ApiBundle\Form\Room\RoomType;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * RoomTypeTags.
 *
 * @ORM\Table(name="room_type_tags")
 * @ORM\Entity
 */
class RoomTypeTags
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
     * @ORM\Column(name="tag_key", type="string", length=64)
     */
    private $tagKey;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=1024)
     */
    private $icon = '';

    /**
     * @var RoomType
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomTypes")
     * @ORM\JoinColumn(name="parent_type_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parentType;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_type_id", type="integer", nullable=true)
     */
    private $parentTypeId;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modification_date", type="datetime")
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
     * Set tagKey.
     *
     * @param string $tagKey
     *
     * @return RoomTypeTags
     */
    public function setTagKey($tagKey)
    {
        $this->tagKey = $tagKey;

        return $this;
    }

    /**
     * Get tagKey.
     *
     * @return string
     */
    public function getTagKey()
    {
        return $this->tagKey;
    }

    /**
     * Set icon.
     *
     * @param string $icon
     *
     * @return RoomTypeTags
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return RoomType
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * @param RoomType $parentType
     */
    public function setParentType($parentType)
    {
        $this->parentType = $parentType;
    }

    /**
     * @return int
     */
    public function getParentTypeId()
    {
        return $this->parentTypeId;
    }

    /**
     * @param int $parentTypeId
     */
    public function setParentTypeId($parentTypeId)
    {
        $this->parentTypeId = $parentTypeId;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return RoomTypeTags
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return RoomTypeTags
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

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
}
