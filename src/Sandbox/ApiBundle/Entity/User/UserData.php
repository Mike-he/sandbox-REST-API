<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * UserData.
 *
 * @ORM\Table(
 *      name="UserData",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="cardNo_UNIQUE", columns={"cardNo"}),
 *          @ORM\UniqueConstraint(name="credentialNo_UNIQUE", columns={"credentialNo"}))
 *      }
 * )
 * @ORM\Entity()
 */
class UserData
{
    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userData")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Serializer\Groups({"main"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="cardNo", type="string", length=32, nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $cardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="credentialNo", type="string", length=64, nullable=true)
     * @Serializer\Groups({"main"})
     */
    private $credentialNo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main"})
     */
    private $modificationDate;

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return UserData
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set cardNo.
     *
     * @param string $cardNo
     *
     * @return UserData
     */
    public function setCardNo($cardNo)
    {
        $this->cardNo = $cardNo;

        return $this;
    }

    /**
     * Get cardNo.
     *
     * @return string
     */
    public function getCardNo()
    {
        return $this->cardNo;
    }

    /**
     * Set credentialNo.
     *
     * @param string $credentialNo
     *
     * @return UserData
     */
    public function setCredentialNo($credentialNo)
    {
        $this->credentialNo = $credentialNo;

        return $this;
    }

    /**
     * Get credentialNo.
     *
     * @return string
     */
    public function getCredentialNo()
    {
        return $this->credentialNo;
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
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return UserData
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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

    /**
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return UserData
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
