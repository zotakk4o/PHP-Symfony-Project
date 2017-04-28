<?php

namespace VehturiinikShopBundle\Entity;

use Doctrine\Common\Collections\Collection;
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
     * @Assert\NotBlank(groups={"discount"})
     *
     * @Assert\GreaterThanOrEqual(value = 0, message="Discount Should be Equal or Greater Than Zero", groups={"discount"})
     *
     * @Assert\LessThanOrEqual(value=99, message="Discount Cannot be More Than 99%", groups={"discount"})
     */
    private $discount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="discount_added", type="boolean")
     *
     */
    private $discountAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_discount_expires", type="datetime", nullable=true)
     *
     * @Assert\Range(min="+1 day ", max="+3 years +11 months", groups={"discount"})
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
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *
     */
    private $category;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer")
     *
     * @Assert\NotBlank(message="Category Id Field Is Required!")
     *
     */
    private $categoryId;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="VehturiinikShopBundle\Entity\Purchase", mappedBy="product")
     *
     */
    private $buyings;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="VehturiinikShopBundle\Entity\Comment", mappedBy="product")
     *
     * @ORM\OrderBy({"dateAdded" = "DESC"})
     */
    private $comments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime")
     */
    private $dateAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_deleted", type="datetime", nullable=true)
     */
    private $dateDeleted;

    public function __construct()
    {
        $this->discountAdded = false;
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
     * @return Collection
     */
    public function getBuyings(): Collection
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
    public function getSummaryOfDescription($length = 150)
    {
        if(strlen($this->getDescription()) <= $length) return $this->getDescription();
        return substr($this->getDescription(), 0, $length) . "...";
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
        $this->dateDiscountExpires = $dateDiscountExpires;
    }

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->price;
    }

    /**
     * @return Comment[]
     */
    public function getComments()
    {
        $comments = [];
        foreach ($this->comments as $comment){
            if(!$comment->isDeleted())$comments[] = $comment;
        }

        return $comments;
    }

    /**
     * @param Comment $comment
     * @return Product
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    public function isAvailable()
    {
        return $this->getDateDeleted() === null && $this->getQuantity() > 0;
    }

    public function isDeleted()
    {
        return $this->getDateDeleted() !== null;
    }

}

