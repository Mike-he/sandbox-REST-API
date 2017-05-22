<?php

namespace Sandbox\ApiBundle\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * ProductRentSet.
 *
 * @ORM\Table(name="product_rent_set")
 * @ORM\Entity()
 */
class ProductRentSet
{

    const UNIT_MONTH = 'month';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail", "client"})
     */
    private $id;

    /**
     * @var \Sandbox\ApiBundle\Entity\Product\Product
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;

    /**
     * @var float
     *
     * @ORM\Column(name="base_price", type="decimal", precision=10, scale=2)
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail", "client"})
     */
    private $basePrice;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_price", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin_room", "admin_detail", "client"})
     */
    private $unitPrice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="earliest_rent_date", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail"})
     */
    private $earliestRentDate;

    /**
     * @var float
     *
     * @ORM\Column(name="deposit", type="decimal", precision=10, scale=2)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "client_appointment_list"})
     */
    private $deposit;

    /**
     * @var string
     *
     * @ORM\Column(name="rental_info", type="text")
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "client_appointment_detail"})
     */
    private $rentalInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="text", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_room", "client", "admin_detail", "client_appointment_detail"})
     */
    private $filename;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="modificationDate", type="datetime")
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
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return float
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @param float $basePrice
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;
    }

    /**
     * @return string
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param string $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return \DateTime
     */
    public function getEarliestRentDate()
    {
        return $this->earliestRentDate;
    }

    /**
     * @param \DateTime $earliestRentDate
     */
    public function setEarliestRentDate($earliestRentDate)
    {
        $this->earliestRentDate = $earliestRentDate;
    }

    /**
     * @return float
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @param float $deposit
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * @return string
     */
    public function getRentalInfo()
    {
        return $this->rentalInfo;
    }

    /**
     * @param string $rentalInfo
     */
    public function setRentalInfo($rentalInfo)
    {
        $this->rentalInfo = $rentalInfo;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTime $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }
}
