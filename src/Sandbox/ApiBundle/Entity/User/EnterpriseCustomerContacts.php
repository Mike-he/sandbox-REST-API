<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * EnterpriseCustomerContacts.
 *
 * @ORM\Table(name="enterprise_customer_contacts")
 * @ORM\Entity
 */
class EnterpriseCustomerContacts
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
     * @ORM\Column(name="enterprise_customer_id", type="integer")
     */
    private $enterpriseCustomerId;

    /**
     * @var int
     *
     * @ORM\Column(name="customer_id", type="integer")
     */
    private $customerId;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_position", type="string", length=255, nullable=true)
     */
    private $contactPosition;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    private $userCustomer;

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
     * Set enterpriseCustomerId.
     *
     * @param int $enterpriseCustomerId
     *
     * @return EnterpriseCustomerContacts
     */
    public function setEnterpriseCustomerId($enterpriseCustomerId)
    {
        $this->enterpriseCustomerId = $enterpriseCustomerId;

        return $this;
    }

    /**
     * Get enterpriseCustomerId.
     *
     * @return int
     */
    public function getEnterpriseCustomerId()
    {
        return $this->enterpriseCustomerId;
    }

    /**
     * Set customerId.
     *
     * @param int $customerId
     *
     * @return EnterpriseCustomerContacts
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set contactPosition.
     *
     * @param string $contactPosition
     *
     * @return EnterpriseCustomerContacts
     */
    public function setContactPosition($contactPosition)
    {
        $this->contactPosition = $contactPosition;

        return $this;
    }

    /**
     * Get contactPosition.
     *
     * @return string
     */
    public function getContactPosition()
    {
        return $this->contactPosition;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return EnterpriseCustomerContacts
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
     * Set userCustomer.
     *
     * @param $userCustomer
     *
     * @return EnterpriseCustomerContacts
     */
    public function setUserCustomer($userCustomer)
    {
        $this->userCustomer = $userCustomer;

        return $this;
    }

    /**
     * Get userCustomer.
     *
     * @return mixed
     */
    public function getUserCustomer()
    {
        return $this->userCustomer;
    }
}
