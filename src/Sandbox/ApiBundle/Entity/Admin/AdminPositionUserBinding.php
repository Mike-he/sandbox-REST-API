<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * AdminPositionUserBinding.
 *
 * @ORM\Table(
 *     name="admin_position_user_binding",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="userId_positionId_buildingId_shopId_UNIQUE", columns={"userId", "positionId", "buildingId", "shopId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminPositionUserBinding_userId_idx", columns={"userId"}),
 *          @ORM\Index(name="fk_AdminPositionUserBinding_positionId_idx", columns={"positionId"}),
 *          @ORM\Index(name="fk_AdminPositionUserBinding_buildingId_idx", columns={"buildingId"}),
 *          @ORM\Index(name="fk_AdminPositionUserBinding_ShopId_idx", columns={"shopId"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Admin\AdminPositionUserBindingRepository")
 */
class AdminPositionUserBinding
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"admin_position_bind_view"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Groups({"admin_position_bind_view"})
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\User\User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main", "admin_view"})
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="positionId", type="integer")
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Groups({"admin_position_bind_view"})
     */
    private $positionId;

    /**
     * @ORM\ManyToOne(targetEntity="AdminPosition")
     * @ORM\JoinColumn(name="positionId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"admin_position_bind_view"})
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"admin_position_bind_view"})
     */
    private $buildingId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumn(name="buildingId", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Serializer\Groups({"admin_position_bind_view"})
     */
    private $building;

    /**
     * @var int
     *
     * @ORM\Column(name="shopId", type="integer", nullable=true)
     *
     * @Serializer\Groups({"admin_position_bind_view"})
     */
    private $shopId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Shop\Shop")
     * @ORM\JoinColumn(name="shopId", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Serializer\Groups({"admin_position_bind_view"})
     */
    private $shop;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"admin_position_bind_view"})
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return AdminPositionUserBinding
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
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
     * Set positionId.
     *
     * @param int $positionId
     *
     * @return AdminPositionUserBinding
     */
    public function setPositionId($positionId)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
     * Get positionId.
     *
     * @return int
     */
    public function getPositionId()
    {
        return $this->positionId;
    }

    /**
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param int $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * @return mixed
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return mixed
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param mixed $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminPositionUserBinding
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
