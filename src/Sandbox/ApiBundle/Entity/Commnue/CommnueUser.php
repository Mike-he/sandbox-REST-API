<?php

namespace Sandbox\ApiBundle\Entity\Commnue;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommnueUser
 *
 * @ORM\Table(name="commune_user")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Commnue\CommueUserRepository")
 */
class CommnueUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="auth_tag_id", type="integer", nullable=true)
     */
    private $authTagId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_banned", type="boolean")
     */
    private $isBanned;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime")
     */
    private $modificationDate;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return CommnueUser
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set authTagId
     *
     * @param integer $authTagId
     * @return CommnueUser
     */
    public function setAuthTagId($authTagId)
    {
        $this->authTagId = $authTagId;

        return $this;
    }

    /**
     * Get authTagId
     *
     * @return integer 
     */
    public function getAuthTagId()
    {
        return $this->authTagId;
    }

    /**
     * Set isBanned
     *
     * @param boolean $isBanned
     * @return CommnueUser
     */
    public function setIsBanned($isBanned)
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    /**
     * Get isBanned
     *
     * @return boolean 
     */
    public function getIsBanned()
    {
        return $this->isBanned;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return CommnueUser
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     * @return CommnueUser
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime 
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }
}
