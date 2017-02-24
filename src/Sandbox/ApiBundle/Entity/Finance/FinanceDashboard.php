<?php

namespace Sandbox\ApiBundle\Entity\Finance;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FinanceDashboard.
 *
 * @ORM\Table(name="finance_dashboard")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Finance\FinanceDashboardRepository")
 */
class FinanceDashboard
{
    const TYPE_CASH_FLOW = 'cash_flow';
    const TYPE_BALANCE_FLOW = 'balance_flow';

    const START_YEAR = 2017;
    const START_MONTH = 1;

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
     * @ORM\Column(name="time_period", type="string", length=64)
     */
    private $timePeriod;

    /**
     * @var string
     *
     * @ORM\Column(name="parameter_key", type="string", length=255)
     */
    private $parameterKey;

    /**
     * @var string
     *
     * @ORM\Column(name="parameter_value", type="string", length=255)
     */
    private $parameterValue;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="creation_date", type="datetime")
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
     * @return string
     */
    public function getTimePeriod()
    {
        return $this->timePeriod;
    }

    /**
     * @param string $timePeriod
     */
    public function setTimePeriod($timePeriod)
    {
        $this->timePeriod = $timePeriod;
    }

    /**
     * Set parameterKey.
     *
     * @param string $parameterKey
     *
     * @return FinanceDashboard
     */
    public function setParameterKey($parameterKey)
    {
        $this->parameterKey = $parameterKey;

        return $this;
    }

    /**
     * Get parameterKey.
     *
     * @return string
     */
    public function getParameterKey()
    {
        return $this->parameterKey;
    }

    /**
     * Set parameterValue.
     *
     * @param string $parameterValue
     *
     * @return FinanceDashboard
     */
    public function setParameterValue($parameterValue)
    {
        $this->parameterValue = $parameterValue;

        return $this;
    }

    /**
     * Get parameterValue.
     *
     * @return string
     */
    public function getParameterValue()
    {
        return $this->parameterValue;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return FinanceDashboard
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return FinanceDashboard
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
}
