<?php

namespace VehturiinikShopBundle\Entity;


use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="VehturiinikShopBundle\Repository\CategoryRepository")
 */
class Category
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
     * @Assert\NotBlank(message="Category Name is Mandatory!")
     *
     * @Assert\Length(min="3")
     */
    private $name;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="VehturiinikShopBundle\Entity\Product", mappedBy="category")
     */
    private $products;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Assert\NotBlank(message="Category Description is Mandatory!")
     *
     * @Assert\Length(min="5")
     *
     */
    private $description;

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
        $this->dateAdded = new \DateTime('now');
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
     * Set name
     *
     * @param mixed $name
     *
     * @return Category
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
     * @return Product[]
     */
    public function getValidProducts()
    {
        $products = [];
        foreach ($this->products as $product){
            if(!$product->getQuantity() == 0 && $product->getDateDeleted() === null)$products[] = $product;
        }
        return $products;
    }

    /**
     * @return Product[]
     */
    public function getAllProducts()
    {
        $products = [];
        foreach ($this->products as $product){
            if($product->isAvailable())$products[] = $product;
        }
        return $products;
    }

    /**
     * @param Product $product
     *
     * @return Category
     */
    public function AddProduct(Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool|string
     */
    public function getSummaryOfDescription($length = 100)
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

    public function getProductsCount()
    {
        return count($this->getValidProducts());
    }

    /**
     * Set Description
     *
     * @param mixed $description
     *
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function isAvailable()
    {
        return $this->getDateDeleted() === null;
    }



}

