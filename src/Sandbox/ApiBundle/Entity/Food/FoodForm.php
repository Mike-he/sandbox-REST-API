<?php

namespace Sandbox\ApiBundle\Entity\Food;

use Doctrine\ORM\Mapping as ORM;

/**
 * FoodForm.
 *
 * @ORM\Table(name="FoodForm")
 * @ORM\Entity
 */
class FoodForm
{
    const FOOD_FORM_TYPE_CUP = 'cup_size';
    const FOOD_FORM_TYPE_SINGLE = 'single';
    const FOOD_FORM_TYPE_MULTIPLE = 'multiple';

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
     * @ORM\Column(name="foodId", type="integer")
     */
    private $foodId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Food\Food
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Food\Food")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foodId", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $food;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *     targetEntity="Sandbox\ApiBundle\Entity\Food\FoodFormOption",
     *     mappedBy="form",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="formId")
     * })
     */
    private $options;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64)
     */
    private $type;

    /**
     * @var bool
     *
     * @ORM\Column(name="required", type="boolean")
     */
    private $required = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     */
    private $modificationDate;

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
     * Set foodId.
     *
     * @param int $foodId
     *
     * @return FoodForm
     */
    public function setFoodId($foodId)
    {
        $this->foodId = $foodId;

        return $this;
    }

    /**
     * Get foodId.
     *
     * @return int
     */
    public function getFoodId()
    {
        return $this->foodId;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return FoodForm
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return FoodForm
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
     * Set required.
     *
     * @param bool $required
     *
     * @return FoodForm
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required.
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Get Food.
     *
     * @return Food
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * Set Food.
     *
     * @param Food $food
     *
     * @return FoodForm
     */
    public function setFood($food)
    {
        $this->food = $food;

        return $this;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options.
     *
     * @param array $options
     *
     * @return FoodForm
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Food
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
     * Set modificationDate.
     *
     * @param \DateTime $modificationDate
     *
     * @return Food
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }
}
