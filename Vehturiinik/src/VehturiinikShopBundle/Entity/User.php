<?php

namespace VehturiinikShopBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="VehturiinikShopBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    const REGULAR_CUSTOMER_PERIOD = 2;
    const USER_DISCOUNT = 10;
    const MONEY_TO_BE_DISCOUNTED = 10000;

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
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(message="Username is required!")
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "Your username must be at least 2 characters long",
     *      maxMessage = "Your userame cannot be longer than 100 characters"
     * )
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     *
     * @Assert\NotBlank(message="Password is required!")
     *
     * @Assert\Length(
     *      min = 5,
     *      minMessage = "Your password must be at least 5 characters long",
     * )
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="full_name", type="string", length=255)
     *
     * @Assert\NotBlank(message="Full Name is required!")
     *
     */
    private $fullName;

    /**
     * @var float
     *
     * @ORM\Column(name="money", type="float")
     */
    private $money;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="VehturiinikShopBundle\Entity\Purchase", mappedBy="user")
     *
     * @ORM\OrderBy({"datePurchased" = "DESC"})
     */
    private $purchases;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="VehturiinikShopBundle\Entity\Role", inversedBy="users")
     * @ORM\JoinTable(name="users_roles",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *     )
     */
    private $roles;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="VehturiinikShopBundle\Entity\Comment", mappedBy="author")
     *
     * @ORM\OrderBy({"dateAdded" = "DESC"})
     */
    private $comments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_registered", type="datetime")
     */
    private $dateRegistered;

    public function __construct()
    {
        $this->dateRegistered = new \DateTime('now');
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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     *
     * @return string[]|Collection
     */
    public function getRoles()
    {
        $result = [];
        $roles = $this->roles;
        foreach ($roles as $role){
            if(gettype($role) == 'string')$result[] = $role;
            else $result[] = $role->getName();
        }

        return $result;
    }

    /**
     * @return Purchase[]
     */
    public function getPurchases()
    {
        $purchases = [];
        foreach ($this->purchases as $purchase) {
            if($purchase->isAvailable())$purchases[] = $purchase;
        }
        return $purchases;
    }

    /**
     * @param Purchase $purchase
     */
    public function addPurchase(Purchase $purchase)
    {
        $this->purchases[] = $purchase;
    }

    /**
     * @return mixed
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * @param mixed $money
     * @return User
     */
    public function setMoney($money)
    {
        $this->money = $money;

        return $this;
    }

    /**
     * @param Role $role
     * @return User
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEditor(): bool
    {
        return in_array('ROLE_EDITOR', $this->getRoles());
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    /**
     * @param Role[] $roles
     */
    public function setRoles(array $roles)
    {

        $this->roles = $roles;
    }

    /**
     * @return \DateTime
     */
    public function getDateRegistered(): \DateTime
    {
        return $this->dateRegistered;
    }

    /**
     * @param \DateTime $dateRegistered
     */
    public function setDateRegistered(\DateTime $dateRegistered)
    {
        $this->dateRegistered = $dateRegistered;
    }

    /**
     * @return bool
     */
    public function isRegularCustomer()
    {
        $currentYear = new \DateTime('now');
        $currentYear = $currentYear->format('Y');
        return $currentYear - $this->getDateRegistered()->format('Y')  >= self::REGULAR_CUSTOMER_PERIOD;
    }

    /**
     * @return int
     */
    public function getPurchasesCount()
    {
        return count($this->getPurchases());
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
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;
    }



    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}

