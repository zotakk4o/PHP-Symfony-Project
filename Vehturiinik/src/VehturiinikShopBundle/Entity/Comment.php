<?php

namespace VehturiinikShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment
 *
 * @ORM\Table(name="comments")
 * @ORM\Entity(repositoryClass="VehturiinikShopBundle\Repository\CommentRepository")
 */
class Comment
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
     * @ORM\Column(name="content", type="text")
     *
     * @Assert\NotBlank(message="Content Field Is Required!")
     *
     * @Assert\Length(min="5")
     */
    private $content;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="VehturiinikShopBundle\Entity\User", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="authorId", referencedColumnName="id")
     */
    private $author;

    /**
     * @var int
     *
     * @ORM\Column(name="authorId", type="integer")
     *
     * @Assert\NotBlank(message="Author Id Field Is Required!")
     *
     */
    private $authorId;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="VehturiinikShopBundle\Entity\Product", inversedBy="comments")
     *
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(name="productId", type="integer")
     *
     * @Assert\NotBlank(message="Product Id Field Is Required!")
     *
     */
    private $productId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAdded", type="datetime")
     *
     */
    private $dateAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDeleted", type="datetime", nullable=true)
     *
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
     * Set content
     *
     * @param mixed $content
     *
     * @return Comment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
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
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
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
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return bool|string
     */
    public function getSummaryOfComment($length = 200)
    {
        if(strlen($this->getContent()) <= $length) return $this->getContent();
        return substr($this->getContent(), 0, $length) . "...";
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;
    }

    /**
     * @return int
     */
    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    /**
     * @param mixed $authorId
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
    }

    /**
     * @param User|null $user
     * @return bool
     */
    public function isAuthor(User $user = null):bool
    {
        return $user && $user->getId() == $this->getAuthorId();
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

    public function isDeleted()
    {
        return $this->getDateDeleted() !== null;
    }

}

