<?php

namespace VehturiinikShopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="VehturiinikShopBundle\Repository\ProductRepository")
 */
class Product
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(message="Name Field Is Required!")
     *
     * @Assert\Length(min="3")
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     *
     * @Assert\NotBlank(message="Price Field Is Required!")
     *
     * @Assert\GreaterThan(value=0, message="Price Cannot be Zero or Negative")
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Assert\NotBlank(message="Description Field Is Required!")
     *
     * @Assert\Length(min="5")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="discount", type="integer")
     *
     * @Assert\NotBlank()
     *
     * @Assert\GreaterThanOrEqual(value = 0, message="Discount Should be Equal or Greater Than Zero")
     *
     * @Assert\LessThanOrEqual(value=99, message="Discount Cannot be More Than 99%")
     */
    private $discount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="discountAdded", type="boolean")
     *
     */
    private $discountAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDiscountExpires", type="datetime", nullable=true)
     *
     * @Assert\Range(min="+1 day", max="+3 years +11 months")
     */
    private $dateDiscountExpires;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     *
     * @Assert\NotBlank()
     *
     * @Assert\GreaterThanOrEqual(value = 0, message="Quantity Should be Equal or Greater Than Zero")
     */
    private $quantity;


    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="VehturiinikShopBundle\Entity\Category", inversedBy="products")
     *
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id")
     *
     */
    private $category;

    /**
     * @var int
     *
     * @ORM\Column(name="categoryId", type="integer")
     *
     * @Assert\NotBlank(message="Category Id Field Is Required!")
     *
     * @Assert\GreaterThanOrEqual(value = 0, message="Quantity Should be Equal or Greater Than Zero")
     */
    private $categoryId;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="VehturiinikShopBundle\Entity\Purchase", mappedBy="product")
     *
     */
    private $buyings;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAdded", type="datetime")
     */
    private $dateAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDeleted", type="datetime", nullable=true)
     */
    private $dateDeleted;

    public function __construct()
    {
        $this->discountAdded = false;
        $this->users = new ArrayCollection();
        $this->dateAdded = new \DateTime('now');
    }


    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param mixed $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @param mixed $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        if($this->getDiscount() == 0)return $this->price;
        return $this->price - ($this->price * $this->getDiscount() / 100);
    }

    /**
     * Set description
     *
     * @param mixed $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set discount
     *
     * @param mixed $discount
     *
     * @return Product
     */
    public function setDiscount($discount)
    {
        if(!$this->isDiscountAdded()){
            $this->setDateDiscountExpires(null);
            $discount = 0;
        }

        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return string
     */
    public function getDiscount()
    {
        if($this->getDateDiscountExpires() !== null && new \DateTime('now') > $this->getDateDiscountExpires()){
            $this->setDateDiscountExpires(null);
            $this->setDiscount(0);
        }
        return $this->discount;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     *
     * @return Product
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     *
     * @return Product
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param int|null $categoryId
     *
     * @return Product
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getBuyings(): ArrayCollection
    {
        return $this->buyings;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded(): \DateTime
    {
        return $this->dateAdded;
    }

    /**
     * @return bool|string
     */
    public function getSummaryOfDescription()
    {
        return substr($this->getDescription(), 0, 300);
    }

    /**
     * @return \DateTime|null
     */
    public function getDateDeleted()
    {
        return $this->dateDeleted;
    }

    /**
     * @param \DateTime|null $dateDeleted
     */
    public function setDateDeleted(\DateTime $dateDeleted)
    {
        $this->dateDeleted = $dateDeleted;
    }

    /**
     * @return bool
     */
    public function isDiscountAdded(): bool
    {
        return $this->discountAdded;
    }

    /**
     * @param bool $discountAdded
     */
    public function setDiscountAdded(bool $discountAdded)
    {
        $this->discountAdded = $discountAdded;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateDiscountExpires()
    {
        return $this->dateDiscountExpires;
    }

    /**
     * @param \DateTime|null $dateDiscountExpires
     */
    public function setDateDiscountExpires($dateDiscountExpires)
    {
        if(!$this->isDiscountAdded())$dateDiscountExpires = null;

        elseif($dateDiscountExpires === null && $this->dateDiscountExpires !== null){
            $this->setDiscount(0);
        }
        $this->dateDiscountExpires = $dateDiscountExpires;
    }

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->price;
    }

}

