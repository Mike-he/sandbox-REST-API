<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ServicetFormOption.
 *
 * @ORM\Table(name = "service_form_option")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Service\ServiceFormOptionRepository")
 */
class ServiceFormOption
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
     *      "admin_service",
     *      "client_service"
     * })
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="form_id", type="integer", nullable=false)
     *
     * @Serializer\Groups({
     *      "main"
     * })
     */
    private $formId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Service\ServiceForm
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Service\ServiceForm")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="form_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
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
     *      "admin_service",
     *      "client_service"
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
     * @return ServiceFormOption
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
     * @return ServiceFormOption
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
     * Set content.
     *
     * @param string $content
     *
     * @return ServiceFormOption
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
