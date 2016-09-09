<?php

namespace Sandbox\ApiBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * EventFormOption.
 *
 * @ORM\Table(name = "event_form_option")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Event\EventFormOptionRepository")
 */
class EventFormOption
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="formId", type="integer", nullable=false)
     *
     * @Serializer\Groups({
     *      "main"
     * })
     */
    private $formId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Event\EventForm
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Event\EventForm")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="formId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({
     *      "main"
     * })
     */
    private $form;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     *
     * @Serializer\Groups({
     *      "main",
     *      "admin_event",
     *      "client_event"
     * })
     */
    private $content;

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
     * Set formId.
     *
     * @param int $formId
     *
     * @return EventFormOption
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
     * @param $form
     *
     * @return EventForm
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form.
     *
     * @return EventFormOption
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return EventFormOption
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
