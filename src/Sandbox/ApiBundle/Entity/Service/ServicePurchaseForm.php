<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServicePurchaseForm.
 *
 * @ORM\Table(name="service_purchase_form")
 * @ORM\Entity
 */
class ServicePurchaseForm
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
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Service\ServiceOrder
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Service\ServiceOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $registration;

    /**
     * @var int
     *
     * @ORM\Column(name="form_Id", type="integer", nullable=false)
     */
    private $formId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Event\EventForm
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Service\ServiceForm")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="form_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $form;

    /**
     * @var string
     *
     * @ORM\Column(name="user_input", type="text", nullable=false)
     */
    private $userInput;

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
     * Set registrationId.
     *
     * @param int $orderId
     *
     * @return ServicePurchaseForm
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get RegistrationId.
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set order.
     *
     * @param $order
     *
     * @return ServicePurchaseForm
     */
    public function setRegistration($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get registration.
     *
     * @return ServiceOrder
     */
    public function getorder()
    {
        return $this->order;
    }

    /**
     * Set formId.
     *
     * @param int $formId
     *
     * @return ServicePurchaseForm
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;

        return $this;
    }

    /**
     * Get formId.
     *
     * @return int
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Set form.
     *
     * @param ServiceForm $form
     *
     * @return ServicePurchaseForm
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form.
     *
     * @return ServiceForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set userInput.
     *
     * @param string $userInput
     *
     * @return ServicePurchaseForm
     */
    public function setUserInput($userInput)
    {
        $this->userInput = $userInput;

        return $this;
    }

    /**
     * Get userInput.
     *
     * @return string
     */
    public function getUserInput()
    {
        return $this->userInput;
    }
}
