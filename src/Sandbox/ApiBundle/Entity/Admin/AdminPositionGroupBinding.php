<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdminPositionGroupBinding.
 *
 * @ORM\Table(
 *     name="admin_position_group_binding",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="group_position_UNIQUE", columns={"group_id", "position_id"})
 *     },
 *     indexes={
 *          @ORM\Index(name="fk_adminPositionGroup_group_idx", columns={"group_id"}),
 *          @ORM\Index(name="fk_adminPositionGroup_permission_idx", columns={"position_id"})
 *     }
 * )
 * @ORM\Entity
 */
class AdminPositionGroupBinding
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
     * @ORM\ManyToOne(targetEntity="AdminPosition")
     * @ORM\JoinColumn(name="position_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPermissionGroups")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $group;

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
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminPositionGroupBinding
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
