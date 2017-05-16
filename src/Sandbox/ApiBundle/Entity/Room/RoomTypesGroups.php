<?php

namespace Sandbox\ApiBundle\Entity\Room;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * RoomTypesGroups.
 *
 * @ORM\Table(name="room_types_groups")
 * @ORM\Entity
 */
class RoomTypesGroups
{
    const TRANS_GROUPS_PREFIX = 'room.type_group.';

    const KEY_MEETING = 'meeting';
    const KEY_DESK = 'desk';
    const KEY_OFFICE = 'office';
    const KEY_OTHERS = 'others';

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
     * @ORM\Column(name="group_key", type="string", length=64)
     */
    private $groupKey;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="homepage_icon", type="string", length=255)
     */
    private $homepageIcon;

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
     * Set groupKey.
     *
     * @param string $groupKey
     *
     * @return RoomTypesGroups
     */
    public function setGroupKey($groupKey)
    {
        $this->groupKey = $groupKey;

        return $this;
    }

    /**
     * Get groupKey.
     *
     * @return string
     */
    public function getGroupKey()
    {
        return $this->groupKey;
    }

    /**
     * Set icon.
     *
     * @param string $icon
     *
     * @return RoomTypesGroups
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
     * @return string
     */
    public function getHomepageIcon()
    {
        return $this->homepageIcon;
    }

    /**
     * @param string $homepageIcon
     */
    public function setHomepageIcon($homepageIcon)
    {
        $this->homepageIcon = $homepageIcon;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return RoomTypesGroups
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
     * @return RoomTypesGroups
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
