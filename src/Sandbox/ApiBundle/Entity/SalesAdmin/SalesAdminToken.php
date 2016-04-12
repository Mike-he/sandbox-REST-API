<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * SalesAdminToken.
 *
 * @ORM\Table(
 *      name="SalesAdminToken",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="token_UNIQUE", columns={"token"}),
 *          @ORM\UniqueConstraint(name="adminId_clientId_UNIQUE", columns={"adminId", "clientId"})
 *      },
 *      indexes={
 *          @ORM\Index(name="fk_AdminToken_adminId_idx", columns={"adminId"}),
 *          @ORM\Index(name="fk_AdminToken_clientId_idx", columns={"clientId"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="Sandbox\ApiBundle\Repository\SalesAdmin\SalesAdminTokenRepository"
 * )
 */
class SalesAdminToken
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"main", "login"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="adminId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $adminId;

    /**
     * @ORM\ManyToOne(targetEntity="SalesAdmin")
     * @ORM\JoinColumn(name="adminId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $admin;

    /**
     * @var int
     *
     * @ORM\Column(name="clientId", type="integer", nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $clientId;

    /**
     * @ORM\ManyToOne(targetEntity="SalesAdminClient")
     * @ORM\JoinColumn(name="clientId", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, nullable=false)
     * @Serializer\Groups({"main", "login"})
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     * @Serializer\Groups({"main", "login"})
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
     * Get adminId.
     *
     * @return int
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * Set adminId.
     *
     * @param int $adminId
     *
     * @return SalesAdminToken
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;
    }

    /**
     * Get clientId.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientId.
     *
     * @param int $clientId
     *
     * @return SalesAdminToken
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token.
     *
     * @param string $token
     *
     * @return SalesAdminToken
     */
    public function setToken($token)
    {
        $this->token = $token;
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
     * @return SalesAdminToken
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get admin.
     *
     * @return SalesAdmin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set admin.
     *
     * @param SalesAdmin $admin
     *
     * @return SalesAdminToken
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * Get client.
     *
     * @return SalesAdminClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set client.
     *
     * @param SalesAdminClient $client
     *
     * @return SalesAdminToken
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}