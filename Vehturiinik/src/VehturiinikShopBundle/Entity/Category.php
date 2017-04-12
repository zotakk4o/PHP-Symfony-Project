<?php

namespace VehturiinikShopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="category")
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
     */
    private $name;

    /**
     * @var ArrayCollection
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
        $this->products = new ArrayCollection();

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
     * @param string $name
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
            if($product->getDateDeleted() === null)$products[] = $product;
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

    public function getProductsCount()
    {
        return count($this->getValidProducts());
    }

    /**
     * Set Description
     *
     * @param string $description
     *
     * @return Category
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }



}

