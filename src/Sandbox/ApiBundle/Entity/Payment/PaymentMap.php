<?php

namespace Sandbox\ApiBundle\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;

/**
 * Payment.
 *
 * @ORM\Table(name="payment_map")
 * @ORM\Entity
 */
class PaymentMap
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
     * @var \Sandbox\ApiBundle\Entity\Payment\Payment
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Payment\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $payment;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=128)
     */
    private $type;

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
     * @return \Sandbox\ApiBundle\Entity\Payment\Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param \Sandbox\ApiBundle\Entity\Payment\Payment $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
