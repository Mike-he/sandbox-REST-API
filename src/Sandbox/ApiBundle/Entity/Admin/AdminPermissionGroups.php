<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdminPermissionGroup.
 *
 * @ORM\Table(name="admin_permission_groups")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Admin\AdminPermissionGroupRepository")
 */
class AdminPermissionGroups
{
    const GROUP_KEY_EVENT = 'activity';
    const GROUP_KEY_TRADE = 'trade';
    const GROUP_KEY_FINANCE = 'finance';
    const GROUP_KEY_REFUND = 'refund';
    const GROUP_KEY_SALES = 'sales';

    const GROUP_PLATFORM_OFFICIAL = 'official';
    const GROUP_PLATFORM_SALES = 'sales';

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
     * @ORM\Column(name="group_key", type="string", length=32)
     */
    private $groupKey;

    /**
     * @var string
     *
     * @ORM\Column(name="group_name", type="string", length=64)
     */
    private $groupName;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=32)
     */
    private $platform;

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
     * Set groupKey.
     *
     * @param string $groupKey
     *
     * @return AdminPermissionGroups
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminPermissionGroups
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @param string $groupName
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
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
