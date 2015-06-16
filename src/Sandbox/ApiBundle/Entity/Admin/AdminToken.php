<?php

namespace Sandbox\ApiBundle\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminToken
 *
 * @ORM\Table(
 *      name="AdminToken",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="token_UNIQUE", columns={"token"}),
 *          @ORM\UniqueConstraint(name="username_clientId_UNIQUE", columns={"username", "clientId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminToken_username_idx", columns={"username"}),
 *          @ORM\Index(name="fk_AdminToken_clientId_idx", columns={"clientId"})
 *      }
 * )
 * @ORM\Entity
 */
class AdminToken
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, nullable=false)
     */
    private $username;

    /**
     * @var integer
     *
     * @ORM\Column(name="clientId", type="integer", nullable=false)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, nullable=false)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param  string     $username
     * @return AdminToken
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get clientId
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientId
     *
     * @param  int        $clientId
     * @return AdminToken
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param  string     $token
     * @return AdminToken
     */
    public function setToken($token)
    {
        $this->token = $token;
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
     * Set creationDate
     *
     * @param  \DateTime  $creationDate
     * @return AdminToken
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
