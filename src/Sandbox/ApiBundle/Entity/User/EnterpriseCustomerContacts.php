<?php

namespace Sandbox\ApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * EnterpriseCustomerContacts
 *
 * @ORM\Table(name="enterprise_customer_contacts")
 * @ORM\Entity
 */
class EnterpriseCustomerContacts
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
     * @ORM\Column(name="enterprise_customer_id", type="integer")
     */
    private $enterpriseCustomerId;

    /**
     * @var integer
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
     * Set enterpriseCustomerId
     *
     * @param integer $enterpriseCustomerId
     * @return EnterpriseCustomerContacts
     */
    public function setEnterpriseCustomerId($enterpriseCustomerId)
    {
        $this->enterpriseCustomerId = $enterpriseCustomerId;

        return $this;
    }

    /**
     * Get enterpriseCustomerId
     *
     * @return integer 
     */
    public function getEnterpriseCustomerId()
    {
        return $this->enterpriseCustomerId;
    }

    /**
     * Set customerId
     *
     * @param integer $customerId
     * @return EnterpriseCustomerContacts
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return integer 
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set contactPosition
     *
     * @param string $contactPosition
     * @return EnterpriseCustomerContacts
     */
    public function setContactPosition($contactPosition)
    {
        $this->contactPosition = $contactPosition;

        return $this;
    }

    /**
     * Get contactPosition
     *
     * @return string 
     */
    public function getContactPosition()
    {
        return $this->contactPosition;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return EnterpriseCustomerContacts
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
}
