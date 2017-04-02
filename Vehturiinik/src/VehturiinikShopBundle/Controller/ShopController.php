<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Entity\Product;

class ShopController extends Controller
{

    /**
     * @Route("/shop", name="view_shop")
     */
   public function viewCategoriesAction()
   {
       //$repository = $this->getDoctrine()->getRepository(Product::class);
       //$products = $repository->createQueryBuilder('p')->where('p.quantity > 0')->getQuery()->getResult();

       $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

       return $this->render('shop/categories.html.twig',['categories' => $categories]);
   }

    /**
     * @param $id
     * @Route("shop/category/{id}", name="view_products_in_category")
     * @return Response
     */
   public function viewProductsInCategoryAction($id)
   {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        $products = $category->getProducts();

        return $this->render('shop/products.html.twig', ['products' => $products]);
   }
}
