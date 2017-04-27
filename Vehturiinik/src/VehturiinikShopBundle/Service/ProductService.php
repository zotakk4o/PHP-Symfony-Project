<?php
/**
 * Created by PhpStorm.
 * User: zotak
 * Date: 4/27/2017
 * Time: 9:55 PM
 */

namespace VehturiinikShopBundle\Service;


use Doctrine\ORM\EntityManager;
use VehturiinikShopBundle\Entity\Product;

class ProductService
{
    private $em;


    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    public function clearInvalidDiscounts()
    {
        foreach ($this->em->getRepository(Product::class)->findAllAtDiscount() as $product){
            if($product->getDateDiscountExpires() !== null && new \DateTime('now') > $product->getDateDiscountExpires()){
                $product->setDateDiscountExpires(null);
                $product->setDiscount(0);
                $product->setDiscountAdded(false);

                $this->em->flush();
            }
        }

    }
}