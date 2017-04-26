<?php

namespace VehturiinikShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Purchase
 *
 * @ORM\Table(name="purchases")
 * @ORM\Entity(repositoryClass="VehturiinikShopBundle\Repository\PurchaseRepository")
 */
class Purchase
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
     * @ORM\Column(name="quantityBought", type="integer")
     *
     * @Assert\GreaterThanOrEqual(value = 0, message="Quantity Should be Equal or Greater Than Zero")
     *
     * @Assert\NotBlank(message="Quantity Cannot be Empty")
     *
     */
    private $quantityBought;

    /**
     * @var int
     *
     * @ORM\Column(name="currentQuantity", type="integer")
     *
     * @Assert\GreaterThanOrEqual(value = 0, message="Quantity Should be Equal or Greater Than Zero")
     *
     * @Assert\NotBlank(message="Quantity Cannot be Empty")
     *
     */
    private $currentQuantity;

    /**
     * @var int
     *
     * @ORM\Column(name="quantityForSale", type="integer")
     *
     * @Assert\GreaterThanOrEqual(value = 0, message="Quantity for Sale Should be Equal or Greater Than Zero")
     *
     * @Assert\NotBlank(message="Quantity for Sale Cannot be Empty")
     *
     */
    private $quantityForSale;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="VehturiinikShopBundle\Entity\User", inversedBy="purchases")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     *
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="userId", type="integer")
     *
     * @Assert\NotBlank(message="User Id Cannot be Empty")
     *
     * @Assert\GreaterThan(value = 0, message="User Id Should be Greater Than Zero")
     */
    private $userId;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="VehturiinikShopBundle\Entity\Product", inversedBy="buyings")
     *
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     *
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer")
     *
     * @Assert\NotBlank(message="Product Id Cannot be Empty")
     *
     * @Assert\GreaterThan(value = 0, message="Product Id Should be Greater Than Zero")
     */
    private $productId;

    /**
     * @var int
     *
     * @ORM\Column(name="discount",type="integer")
     *
     * @Assert\NotBlank()
     *
     * @Assert\LessThanOrEqual(value="99")
     *
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $discount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datePurchased", type="datetime")
     */
    private $datePurchased;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDeleted", type="datetime", nullable=true)
     */
    private $dateDeleted;

    /**
     * @var float
     *
     * @ORM\Column(name="pricePerPiece", type="float")
     *
     * @Assert\NotBlank(message="Price Field Is Required!")
     *
     * @Assert\GreaterThan(value=0, message="Price Cannot be Zero or Negative")
     */
    private $pricePerPiece;

    public function __construct()
    {
        $this->datePurchased = new \DateTime('now');
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set quantity
     *
     * @param integer $currentQuantity
     *
     * @return Purchase
     */
    public function setCurrentQuantity($currentQuantity)
    {
        if($currentQuantity == 0)$this->setDateDeleted(new \DateTime('now'));
        $this->currentQuantity = $currentQuantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getCurrentQuantity()
    {
        return $this->currentQuantity;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Purchase
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     *
     * @return Purchase
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }


    /**
     * @param Product $product
     *
     * @return Purchase
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     *
     * @return Purchase
     */
    public function setProductId( $productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDatePurchased(): \DateTime
    {
        return $this->datePurchased;
    }

    /**
     * @return int
     */
    public function getQuantityForSale(): int
    {
        return $this->quantityForSale;
    }

    /**
     * @param mixed $quantityForSale
     *
     * @return Purchase
     */
    public function setQuantityForSale($quantityForSale)
    {
        $this->quantityForSale = $quantityForSale;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantityBought(): int
    {
        return $this->quantityBought;
    }

    /**
     * @param mixed $quantityBought
     *
     * @return Purchase
     */
    public function setQuantityBought($quantityBought)
    {
        $this->quantityBought = $quantityBought;

        return $this;
    }



    /**
     * @return int
     */
    public function getDiscount(): int
    {
        return $this->discount;
    }

    /**
     * @param mixed $discount
     *
     * @return Purchase
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return float
     */
    public function getPricePerPiece(): float
    {
        return $this->pricePerPiece;
    }

    /**
     * @param mixed $pricePerPiece
     *
     * @return Purchase
     */
    public function setPricePerPiece($pricePerPiece)
    {
        $this->pricePerPiece = $pricePerPiece;

        return $this;
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
    public function setDateDeleted($dateDeleted)
    {
        $this->dateDeleted = $dateDeleted;
    }

    public function isAvailable()
    {
        return $this->getDateDeleted() === null;
    }

}

