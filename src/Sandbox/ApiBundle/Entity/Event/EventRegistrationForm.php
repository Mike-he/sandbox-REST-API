<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventRegistrationForm.
 *
 * @ORM\Table(name="event_registration_form")
 * @ORM\Entity
 */
class EventRegistrationForm
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
     * @ORM\Column(name="registrationId", type="integer", nullable=false)
     */
    private $registrationId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Event\EventRegistration
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Event\EventRegistration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registrationId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $registration;

    /**
     * @var int
     *
     * @ORM\Column(name="formId", type="integer", nullable=false)
     */
    private $formId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Event\EventForm
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Event\EventForm")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="formId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $form;

    /**
     * @var string
     *
     * @ORM\Column(name="userInput", type="text", nullable=false)
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
     * @param int $registrationId
     *
     * @return EventRegistrationForm
     */
    public function setRegistrationId($registrationId)
    {
        $this->registrationId = $registrationId;

        return $this;
    }

    /**
     * Get registrationId.
     *
     * @return int
     */
    public function getRegistrationId()
    {
        return $this->registrationId;
    }

    /**
     * Set registration.
     *
     * @param $registration
     *
     * @return EventRegistration
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * Get registration.
     *
     * @return EventRegistrationForm
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Set formId.
     *
     * @param int $formId
     *
     * @return EventRegistrationForm
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
     * @param EventForm $form
     *
     * @return EventRegistrationForm
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form.
     *
     * @return EventForm
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
     * @return EventRegistrationForm
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
