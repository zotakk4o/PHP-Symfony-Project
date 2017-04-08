<?php

namespace VehturiinikShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

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
     * @ORM\Column(name="quantity", type="integer", options={"default" = 0})
     */
    private $quantity;

    /**
     * @var int
     *
     * @ORM\Column(name="quantityForSale", type="integer", options={"default" = 0})
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
     */
    private $userId;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="VehturiinikShopBundle\Entity\Product", inversedBy="buyings")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     *
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer")
     */
    private $productId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datePurchased", type="datetime")
     */
    private $datePurchased;

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
     * @param integer $quantity
     *
     * @return Purchase
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
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
     * @param int $productId
     *
     * @return Purchase
     */
    public function setProductId(int $productId)
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
     * @param int $quantityForSale
     *
     * @return Purchase
     */
    public function setQuantityForSale(int $quantityForSale)
    {
        $this->quantityForSale = $quantityForSale;

        return $this;
    }






}
