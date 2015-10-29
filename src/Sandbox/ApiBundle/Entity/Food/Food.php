<?php

namespace Sandbox\ApiBundle\Entity\Food;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JsonSerializable;

/**
 * Food.
 *
 * @ORM\Table(name="Food")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Food\FoodRepository")
 */
class Food implements JsonSerializable
{
    const CATEGORY_DRINK = 'drink';
    const CATEGORY_DESSERT = 'dessert';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="cityId", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $cityId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomCity
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cityId", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $city;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingId", type="integer")
     *
     * @Serializer\Groups({"main"})
     */
    private $buildingId;

    /**
     * @var \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\ApiBundle\Entity\Room\RoomBuilding")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="buildingId", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $building;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255)
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="inventory", type="integer", nullable=true)
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $inventory;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modificationDate", type="datetime")
     *
     * @Serializer\Groups({"main", "admin_detail"})
     */
    private $modificationDate;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Food\FoodAttachment",
     *      mappedBy="food",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="foodId")
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $attachments;

    /**
     * @var array
     *
     * @ORM\OneToMany(
     *      targetEntity="Sandbox\ApiBundle\Entity\Food\FoodForm",
     *      mappedBy="food",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="id", referencedColumnName="foodId")
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @Serializer\Groups({"main", "admin_detail", "client_detail"})
     */
    private $forms;

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
     * Set name.
     *
     * @param string $name
     *
     * @return Food
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Food
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set cityId.
     *
     * @param int $cityId
     *
     * @return Food
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId.
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set city.
     *
     * @param $city
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomCity
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return Food
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set buildingId.
     *
     * @param int $buildingId
     *
     * @return Food
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    /**
     * Get buildingId.
     *
     * @return int
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * Set building.
     *
     * @param $building
     *
     * @return \Sandbox\ApiBundle\Entity\Room\RoomBuilding
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get building.
     *
     * @return Food
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Set category.
     *
     * @param string $category
     *
     * @return Food
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set price.
     *
     * @param string $price
     *
     * @return Food
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set inventory.
     *
     * @param int $inventory
     *
     * @return Food
     */
    public function setInventory($inventory)
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * Get inventory.
     *
     * @return int
     */
    public function getInventory()
    {
        return $this->inventory;
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

    /**
     * Get attachments.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set attachments.
     *
     * @param array $attachments
     *
     * @return Food
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get forms.
     *
     * @return array
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * Set forms.
     *
     * @param array $forms
     *
     * @return Food
     */
    public function setForms($forms)
    {
        $this->forms = $forms;

        return $this;
    }

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->setCreationDate($now);
        $this->setModificationDate($now);
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'city' => $this->city,
            'building' => $this->building,
            'category' => $this->category,
        );
    }

    public function jsonDessert()
    {
        return array(
            'price' => $this->price,
            'inventory' => $this->inventory,
        );
    }
}
