<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use VehturiinikShopBundle\Entity\Product;

class ShopController extends Controller
{

    /**
     * @Route("/shop", name="view_shop")
     */
   public function viewProductsAction()
   {
      $repository = $this->getDoctrine()->getRepository(Product::class);

      $products = $repository->createQueryBuilder('p')->where('p.quantity > 0')->getQuery()->getResult();

      return $this->render('shop/products.html.twig',['products' => $products]);
   }
}
