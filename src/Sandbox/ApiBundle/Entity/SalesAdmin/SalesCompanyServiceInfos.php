<?php

namespace Sandbox\ApiBundle\Entity\SalesAdmin;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalesCompanyServiceInfos.
 *
 * @ORM\Table(name="sales_company_service_infos")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\SalesAdmin\SalesCompanyServiceInfosRepository")
 */
class SalesCompanyServiceInfos
{
    const COLLECTION_METHOD_SANDBOX = 'sandbox';
    const COLLECTION_METHOD_SALES = 'sales';

    const DRAWER_SANDBOX = 'sandbox';
    const DRAWER_SALES = 'sales';

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
     * @ORM\Column(name="room_types", type="string", length=30)
     */
    private $roomTypes;

    /**
     * @var SalesCompany
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $company;

    /**
     * @var float
     *
     * @ORM\Column(name="service_fee", type="float", precision=6, scale=3)
     */
    private $serviceFee;

    /**
     * @var string
     *
     * @ORM\Column(name="collection_method", type="string", length=30, nullable=true)
     */
    private $collectionMethod = self::COLLECTION_METHOD_SANDBOX;

    /**
     * @var string
     *
     * @ORM\Column(name="drawer", type="string", length=30)
     */
    private $drawer = self::DRAWER_SANDBOX;

    /**
     * @var string
     *
     * @ORM\Column(name="invoicing_subjects", type="string", length=60, nullable=true)
     */
    private $invoicingSubjects;

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getRoomTypes()
    {
        return $this->roomTypes;
    }

    /**
     * @param string $roomTypes
     */
    public function setRoomTypes($roomTypes)
    {
        $this->roomTypes = $roomTypes;
    }

    /**
     * @return SalesCompany
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param SalesCompany $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return float
     */
    public function getServiceFee()
    {
        return $this->serviceFee;
    }

    /**
     * @param float $serviceFee
     */
    public function setServiceFee($serviceFee)
    {
        $this->serviceFee = $serviceFee;
    }

    /**
     * @return string
     */
    public function getCollectionMethod()
    {
        return $this->collectionMethod;
    }

    /**
     * @param string $collectionMethod
     */
    public function setCollectionMethod($collectionMethod)
    {
        $this->collectionMethod = $collectionMethod;
    }

    /**
     * @return string
     */
    public function getDrawer()
    {
        return $this->drawer;
    }

    /**
     * @param string $drawer
     */
    public function setDrawer($drawer)
    {
        $this->drawer = $drawer;
    }

    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getInvoicingSubjects()
    {
        return $this->invoicingSubjects;
    }

    /**
     * @param string $invoicingSubjects
     */
    public function setInvoicingSubjects($invoicingSubjects)
    {
        $this->invoicingSubjects = $invoicingSubjects;
    }
}
