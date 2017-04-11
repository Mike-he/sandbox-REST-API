<?php

namespace Sandbox\ApiBundle\Entity\MembershipCard;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MembershipCard.
 *
 * @ORM\Table(name="membership_card_specification")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\MembershipCard\MembershipCardSpecificationRepository")
 */
class MembershipCardSpecification
{
    const UNIT_MONTH = 'month';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var MembershipCard
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\MembershipCard\MembershipCard")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $card;

    /**
     * @var string
     *
     * @ORM\Column(name="specification", type="string", length=64)
     */
    private $specification;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="valid_period", type="integer")
     */
    private $validPeriod;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_price", type="string", length=255)
     */
    private $unitPrice;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MembershipCard
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param MembershipCard $card
     */
    public function setCard($card)
    {
        $this->card = $card;
    }

    /**
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @param string $specification
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getValidPeriod()
    {
        return $this->validPeriod;
    }

    /**
     * @param int $validPeriod
     */
    public function setValidPeriod($validPeriod)
    {
        $this->validPeriod = $validPeriod;
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
