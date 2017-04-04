<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\Purchase;
use VehturiinikShopBundle\Entity\User;

class ShopController extends Controller
{

    /**
     * @Route("/shop", name="view_shop")
     */
   public function viewCategoriesAction()
   {
       $validCategories = [];
       $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
       foreach ($categories as $category){
           if(!empty($category->getProducts()))$validCategories[] = $category;
       }

       return $this->render('shop/categories.html.twig',['categories' => $validCategories]);
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

    /**
     * @Route("/purchases", name="view_purchases")
     */
    public function viewBoughtProductsAction()
    {
        if(!$this->getUser()){
            $this->addFlash('error','Log in in order view your purchases!');
            return $this->redirectToRoute('security_login');
        }
        $userId = $this->getUser()->getId();

        $purchases = $this->getDoctrine()->getRepository(Purchase::class)->findBy(['userId' => $userId]);
        $productsBought = [];
        $quantities = [];

        foreach ($purchases as $purchase){
            $product = $purchase->getProduct();
            $productsBought[$product->getName()] =  $product;
            $quantities[$product->getName()] = $purchase->getQuantity();
        }

        if(empty($productsBought)){
            $this->addFlash('warning','You haven\'t bought any products!');
            return $this->redirectToRoute('view_shop');
        }

        return $this->render('shopping/bought.html.twig', ['products' => $productsBought, 'quantities' => $quantities]);
    }

    /**
     * @param $id int
     * @param $quantity int
     * @Route("/shop/buy/{id}/{quantity}",name="buy_product")
     * @return RedirectResponse
     */
    public function buyProductAction($id, $quantity)
    {
        if(!$this->getUser()){
            $this->addFlash('error','Log in in order to buy products from the shop!');
            return $this->redirectToRoute('security_login');
        }

        /**
         * Retrieve the logged in user in order later to add the product to his purchases list
         *
         * @var $user User
         */
        $user = $this->getUser();

        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if($product === null){
            $this->addFlash('error','This product doesn\'t exist');
            return $this->redirectToRoute('view_shop');
        }

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $productPrice = $product->getPrice();

        if($user->getMoney() - $productPrice * $quantity < 0) {
            $this->addFlash('warning','You don\'t have enough cash to buy this product!');
            return $this->redirectToRoute('view_shop');
        }elseif($product->getQuantity() - $quantity < 0) {
            $this->addFlash('warning','Invalid product quantity!');
            return $this->redirectToRoute('view_shop');
        }

        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->findOneBy(['productId' => $product->getId()]);

        if($purchase !== null){
            $purchase->setQuantity($purchase->getQuantity() + $quantity);
        }else{
            $purchase = new Purchase();
            $purchase->setProduct($product);
            $purchase->setUser($user);
            $purchase->setQuantity($quantity);

            $user->addPurchase($purchase);
        }

        $em->persist($purchase);
        $em->flush();

        $user->setMoney($user->getMoney() - $productPrice * $quantity);
        $product->setQuantity($product->getQuantity() - $quantity);

        $session->remove($product->getName());
        $products = $session->get('products');
        unset($products[$product->getName()]);
        $session->set('products',$products);

        $em->persist($user);
        $em->flush();

        $em->persist($product);
        $em->flush();

        return $this->redirectToRoute('view_purchases');
    }

}
