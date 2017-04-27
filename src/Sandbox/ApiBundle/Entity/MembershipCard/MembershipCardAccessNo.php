<?php

namespace Sandbox\ApiBundle\Entity\MembershipCard;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MembershipCardAccessNo.
 *
 * @ORM\Table(name="membership_card_access_no")
 * @ORM\Entity()
 */
class MembershipCardAccessNo
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
     * @var int
     *
     * @ORM\Column(name="card", type="integer")
     */
    private $card;

    /**
     * @var string
     *
     * @ORM\Column(name="access_no", type="string", length=64)
     */
    private $accessNo;

    /**
     * @var string
     *
     * @ORM\Column(name="building_id", type="integer")
     */
    private $buildingId;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccessNo()
    {
        return $this->accessNo;
    }

    /**
     * @param string $accessNo
     */
    public function setAccessNo($accessNo)
    {
        $this->accessNo = $accessNo;
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
     * @return int
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param int $card
     */
    public function setCard($card)
    {
        $this->card = $card;
    }

    /**
     * @return string
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param string $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }
}
