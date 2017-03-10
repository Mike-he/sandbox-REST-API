<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdminPermission.
 *
 * @ORM\Table(
 *      name="admin_permission",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="key_UNIQUE", columns={"key"})}
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Admin\AdminPermissionRepository")
 */
class AdminPermission
{
    const PERMISSION_LEVEL_GLOBAL = 'global';
    const PERMISSION_LEVEL_SPECIFY = 'specify';

    const PERMISSION_PLATFORM_OFFICIAL = 'official';
    const PERMISSION_PLATFORM_SALES = 'sales';
    const PERMISSION_PLATFORM_SHOP = 'shop';

    const OP_LEVEL_VIEW = 1;
    const OP_LEVEL_EDIT = 2;
    const OP_LEVEL_USER_BANNED = 3;

    const KEY_OFFICIAL_PLATFORM_ORDER = 'platform.order';
    const KEY_OFFICIAL_PLATFORM_USER = 'platform.user';
    const KEY_OFFICIAL_PLATFORM_ROOM = 'platform.room';
    const KEY_OFFICIAL_PLATFORM_PRODUCT = 'platform.product';
    const KEY_OFFICIAL_PLATFORM_PRICE = 'platform.price';
    const KEY_OFFICIAL_PLATFORM_ACCESS = 'platform.access';
    const KEY_OFFICIAL_PLATFORM_ADMIN = 'platform.admin';
    const KEY_OFFICIAL_PLATFORM_ANNOUNCEMENT = 'platform.announcement';
    const KEY_OFFICIAL_PLATFORM_DASHBOARD = 'platform.dashboard';
    const KEY_OFFICIAL_PLATFORM_EVENT = 'platform.event';
    const KEY_OFFICIAL_PLATFORM_BANNER = 'platform.banner';
    const KEY_OFFICIAL_PLATFORM_NEWS = 'platform.news';
    const KEY_OFFICIAL_PLATFORM_MESSAGE = 'platform.message';
    const KEY_OFFICIAL_PLATFORM_BUILDING = 'platform.building';
    const KEY_OFFICIAL_PLATFORM_VERIFY = 'platform.verify';
    const KEY_OFFICIAL_PLATFORM_SALES = 'platform.sales';
    const KEY_OFFICIAL_PLATFORM_BULLETIN = 'platform.bulletin';
    const KEY_OFFICIAL_PLATFORM_INVOICE = 'platform.invoice';
    const KEY_OFFICIAL_PLATFORM_ORDER_RESERVE = 'platform.order.reserve';
    const KEY_OFFICIAL_PLATFORM_ORDER_PREORDER = 'platform.order.preorder';
    const KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY = 'platform.product.appointment';
    const KEY_OFFICIAL_PLATFORM_LOG = 'platform.log';
    const KEY_OFFICIAL_PLATFORM_ADVERTISING = 'platform.advertising';
    const KEY_OFFICIAL_PLATFORM_REFUND = 'platform.order.refund';
    const KEY_OFFICIAL_PLATFORM_FINANCE = 'platform.finance';
    const KEY_OFFICIAL_PLATFORM_WITHDRAWAL = 'platform.withdrawal';
    const KEY_OFFICIAL_PLATFORM_SPACE = 'platform.space';
    const KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE = 'platform.long_term_lease';
    const KEY_OFFICIAL_PLATFORM_LONG_TERM_APPOINTMENT = 'platform.long_term_appointment';
    const KEY_OFFICIAL_PLATFORM_EVENT_ORDER = 'platform.event_order';
    const KEY_OFFICIAL_PLATFORM_SHOP_ORDER = 'platform.shop_order';
    const KEY_OFFICIAL_PLATFORM_TOP_UP = 'platform.top_up';
    const KEY_OFFICIAL_PLATFORM_LONG_TERM_SERVICE_RECEIPT = 'platform.long_term_service_receipt';
    const KEY_OFFICIAL_PLATFORM_SALES_INVOICE_CONFIRM = 'platform.sales_invoice_confirm';
    const KEY_OFFICIAL_PLATFORM_TRANSFER_CONFIRM = 'platform.transfer_confirm';
    const KEY_OFFICIAL_PLATFORM_SALES_MONITORING = 'platform.sales_monitoring';

    const KEY_SALES_PLATFORM_DASHBOARD = 'sales.platform.dashboard';
    const KEY_SALES_PLATFORM_ADMIN = 'sales.platform.admin';
    const KEY_SALES_PLATFORM_BUILDING = 'sales.platform.building';
    const KEY_SALES_PLATFORM_INVOICE = 'sales.platform.invoice';
    const KEY_SALES_PLATFORM_EVENT = 'sales.platform.event';
    const KEY_SALES_PLATFORM_EVENT_ORDER = 'sales.platform.event_order';
    const KEY_SALES_PLATFORM_LONG_TERM_SERVICE_BILLS = 'sales.platform.long_term_service_bills';
    const KEY_SALES_PLATFORM_MONTHLY_BILLS = 'sales.platform.monthly_bills';
    const KEY_SALES_PLATFORM_FINANCIAL_SUMMARY = 'sales.platform.financial_summary';
    const KEY_SALES_PLATFORM_WITHDRAWAL = 'sales.platform.withdrawal';
    const KEY_SALES_PLATFORM_AUDIT = 'sales.platform.audit';
    const KEY_SALES_PLATFORM_ACCOUNT = 'sales.platform.account';
    const KEY_SALES_BUILDING_PRICE = 'sales.building.price';
    const KEY_SALES_BUILDING_ORDER = 'sales.building.order';
    const KEY_SALES_BUILDING_ORDER_RESERVE = 'sales.building.order.reserve';
    const KEY_SALES_BUILDING_ORDER_PREORDER = 'sales.building.order.preorder';
    const KEY_SALES_BUILDING_BUILDING = 'sales.building.building';
    const KEY_SALES_BUILDING_USER = 'sales.building.user';
    const KEY_SALES_BUILDING_ROOM = 'sales.building.room';
    const KEY_SALES_BUILDING_PRODUCT = 'sales.building.product';
    const KEY_SALES_BUILDING_ACCESS = 'sales.building.access';
    const KEY_SALES_BUILDING_SPACE = 'sales.building.space';
    const KEY_SALES_BUILDING_LONG_TERM_LEASE = 'sales.building.long_term_lease';
    const KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT = 'sales.building.long_term_appointment';

    const KEY_SHOP_PLATFORM_DASHBOARD = 'shop.platform.dashboard';
    const KEY_SHOP_PLATFORM_ADMIN = 'shop.platform.admin';
    const KEY_SHOP_PLATFORM_SHOP = 'shop.platform.shop';
    const KEY_SHOP_PLATFORM_SPEC = 'shop.platform.spec';
    const KEY_SHOP_SHOP_SHOP = 'shop.shop.shop';
    const KEY_SHOP_SHOP_ORDER = 'shop.shop.order';
    const KEY_SHOP_SHOP_PRODUCT = 'shop.shop.product';
    const KEY_SHOP_SHOP_KITCHEN = 'shop.shop.kitchen';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic", "admin_position_bind_view"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", length=128, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic", "admin_position_bind_view"})
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic", "admin_position_bind_view"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(name="level", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $level;

    /**
     * @var int
     *
     * @ORM\Column(name="maxOpLevel", type="integer", nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $maxOpLevel;

    /**
     * @var string
     *
     * @ORM\Column(name="opLevelSelect", type="string", length=16, nullable=false)
     * @Serializer\Groups({"main", "login", "admin", "auth", "admin_basic"})
     */
    private $opLevelSelect;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
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
     * Set key.
     *
     * @param string $key
     *
     * @return AdminPermission
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return AdminPermission
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
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return int
     */
    public function getMaxOpLevel()
    {
        return $this->maxOpLevel;
    }

    /**
     * @param int $maxOpLevel
     */
    public function setMaxOpLevel($maxOpLevel)
    {
        $this->maxOpLevel = $maxOpLevel;
    }

    /**
     * @return string
     */
    public function getOpLevelSelect()
    {
        return $this->opLevelSelect;
    }

    /**
     * @param string $opLevelSelect
     */
    public function setOpLevelSelect($opLevelSelect)
    {
        $this->opLevelSelect = $opLevelSelect;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return AdminPermission
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
     * @return AdminPermission
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
