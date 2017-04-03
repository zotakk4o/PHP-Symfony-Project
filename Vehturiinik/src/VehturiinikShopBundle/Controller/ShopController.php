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

        if($category === null){
            $this->addFlash('error','This category doesn\'t exist!');
            return $this->redirectToRoute('view_shop');
        }

       $products = $category->getProducts();

        return $this->render('shop/products.html.twig', ['products' => $products]);
   }
}
