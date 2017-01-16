<?php

namespace Sandbox\ApiBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log.
 *
 * @ORM\Table(
 *     name="log",
 *     indexes={
 *          @ORM\Index(name="salesCompanyId_idx", columns="salesCompanyId"),
 *          @ORM\Index(name="logModule_idx", columns="logModule")
 *     }
 * )
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Log\LogRepository")
 */
class Log
{
    const PLATFORM_OFFICIAL = 'official';
    const PLATFORM_SALES = 'sales';
    const PLATFORM_SHOP = 'shop';

    const MODULE_ADMIN = 'admin';
    const MODULE_BUILDING = 'building';
    const MODULE_ROOM = 'room';
    const MODULE_INVOICE = 'invoice';
    const MODULE_EVENT = 'event';
    const MODULE_PRICE_RULE = 'price_rule';
    const MODULE_ROOM_ORDER = 'room_order';
    const MODULE_ORDER_RESERVE = 'room_order_reserve';
    const MODULE_ORDER_PREORDER = 'room_order_preorder';
    const MODULE_USER = 'user';
    const MODULE_PRODUCT = 'product';
    const MODULE_PRODUCT_APPOINTMENT = 'product_appointment';
    const MODULE_LEASE = 'lease';
    const MODULE_FINANCE = 'finance';

    const ACTION_CREATE = 'create';
    const ACTION_DELETE = 'delete';
    const ACTION_EDIT = 'edit';
    const ACTION_CANCEL = 'cancel';
    const ACTION_AUTHORIZE = 'authorize';
    const ACTION_BAN = 'ban';
    const ACTION_UNBAN = 'unban';
    const ACTION_ON_SALE = 'on_sale';
    const ACTION_OFF_SALE = 'off_sale';
    const ACTION_RECOMMEND = 'recommend';
    const ACTION_REMOVE_RECOMMEND = 'remove_recommend';
    const ACTION_AGREE = 'agree';
    const ACTION_REJECT = 'reject';
    const ACTION_PRIVATE = 'private';
    const ACTION_REMOVE_PRIVATE = 'remove_private';
    const ACTION_CONFORMING = 'conforming';
    const ACTION_CONFORMED = 'conformed';
    const ACTION_PERFORMING = 'performing';
    const ACTION_CLOSE = 'close';
    const ACTION_TERMINATE = 'terminate';
    const ACTION_END = 'end';

    const OBJECT_ADMIN = 'admin';
    const OBJECT_BUILDING = 'building';
    const OBJECT_INVOICE = 'invoice';
    const OBJECT_EVENT = 'event';
    const OBJECT_PRICE_RULE = 'price_rule';
    const OBJECT_ROOM_ORDER = 'room_order';
    const OBJECT_ROOM = 'room';
    const OBJECT_USER = 'user';
    const OBJECT_PRODUCT = 'product';
    const OBJECT_PRODUCT_APPOINTMENT = 'product_appointment';
    const OBJECT_LEASE = 'lease';
    const OBJECT_LEASE_BILL = 'lease_bill';
    const OBJECT_WITHDRAWAL = 'withdrawal';

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
     * @ORM\Column(name="platform", type="string", length=64)
     */
    private $platform;

    /**
     * @var int
     *
     * @ORM\Column(name="salesCompanyId", type="integer", nullable=true)
     */
    private $salesCompanyId;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumn(name="salesCompanyId", referencedColumnName="id", onDelete="SET NULL")
     */
    private $salesCompany;

    /**
     * @var string
     *
     * @ORM\Column(name="adminUsername", type="string", length=64)
     */
    private $adminUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="logModule", type="string", length=64)
     */
    private $logModule;

    /**
     * @var string
     *
     * @ORM\Column(name="logAction", type="string", length=64)
     */
    private $logAction;

    /**
     * @var string
     *
     * @ORM\Column(name="logObjectKey", type="string", length=64)
     */
    private $logObjectKey;

    /**
     * @var int
     *
     * @ORM\Column(name="logObjectId", type="integer")
     */
    private $logObjectId;

    /**
     * @var string
     *
     * @ORM\Column(name="logObjectJson", type="text")
     */
    private $logObjectJson;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="mark", type="boolean", nullable=false)
     */
    private $mark = false;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255, nullable=true)
     */
    private $remarks;

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
     * Set platform.
     *
     * @param string $platform
     *
     * @return Log
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform.
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set salesCompanyId.
     *
     * @param int $salesCompanyId
     *
     * @return Log
     */
    public function setSalesCompanyId($salesCompanyId)
    {
        $this->salesCompanyId = $salesCompanyId;

        return $this;
    }

    /**
     * Get salesCompanyId.
     *
     * @return int
     */
    public function getSalesCompanyId()
    {
        return $this->salesCompanyId;
    }

    /**
     * Set adminUsername.
     *
     * @param string $adminUsername
     *
     * @return Log
     */
    public function setAdminUsername($adminUsername)
    {
        $this->adminUsername = $adminUsername;

        return $this;
    }

    /**
     * Get adminUsername.
     *
     * @return string
     */
    public function getAdminUsername()
    {
        return $this->adminUsername;
    }

    /**
     * Set logModule.
     *
     * @param string $logModule
     *
     * @return Log
     */
    public function setLogModule($logModule)
    {
        $this->logModule = $logModule;

        return $this;
    }

    /**
     * Get logModule.
     *
     * @return string
     */
    public function getLogModule()
    {
        return $this->logModule;
    }

    /**
     * Set logAction.
     *
     * @param string $logAction
     *
     * @return Log
     */
    public function setLogAction($logAction)
    {
        $this->logAction = $logAction;

        return $this;
    }

    /**
     * Get logAction.
     *
     * @return string
     */
    public function getLogAction()
    {
        return $this->logAction;
    }

    /**
     * Set logObjectKey.
     *
     * @param string $logObjectKey
     *
     * @return Log
     */
    public function setLogObjectKey($logObjectKey)
    {
        $this->logObjectKey = $logObjectKey;

        return $this;
    }

    /**
     * Get logObjectKey.
     *
     * @return string
     */
    public function getLogObjectKey()
    {
        return $this->logObjectKey;
    }

    /**
     * Set logObjectJson.
     *
     * @param string $logObjectJson
     *
     * @return Log
     */
    public function setLogObjectJson($logObjectJson)
    {
        $this->logObjectJson = $logObjectJson;

        return $this;
    }

    /**
     * @return int
     */
    public function getLogObjectId()
    {
        return $this->logObjectId;
    }

    /**
     * @param int $logObjectId
     */
    public function setLogObjectId($logObjectId)
    {
        $this->logObjectId = $logObjectId;
    }

    /**
     * Get logObjectJson.
     *
     * @return string
     */
    public function getLogObjectJson()
    {
        return $this->logObjectJson;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Log
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
     * @return bool
     */
    public function isMark()
    {
        return $this->mark;
    }

    /**
     * @param bool $mark
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }

    /**
     * @return string
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * @param string $remarks
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;
    }

    /**
     * @return mixed
     */
    public function getSalesCompany()
    {
        return $this->salesCompany;
    }

    /**
     * @param mixed $salesCompany
     */
    public function setSalesCompany($salesCompany)
    {
        $this->salesCompany = $salesCompany;
    }

    /**
     * Log constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime('now');
    }
}
