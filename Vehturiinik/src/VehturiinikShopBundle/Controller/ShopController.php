<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

        foreach ($purchases as $purchase){
            $product = $purchase->getProduct();
            $productsBought[] =  $product;

        }

        if(empty($productsBought)){
            $this->addFlash('warning','You haven\'t bought any products!');
            return $this->redirectToRoute('view_shop');
        }

        return $this->render('shopping/bought.html.twig', ['purchases' => $purchases]);
    }

    /**
     * @param $purchaseId int
     * @Route("/purchases/sell/purchase/{purchaseId}", name="sell_product")
     * @return Response
     */
    public function sellPurchaseAction($purchaseId)
    {
        /**@var User $user*/
        $user = $this->getUser();
        if(!$user){
            $this->addFlash('error','Cannot access this page!');
            return $this->redirectToRoute('security_login');
        }

        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->find($purchaseId);
        $product = $purchase->getProduct();

        if($purchase === null){
            $this->addFlash('warning','You haven\'t bought this product!');
            return $this->redirectToRoute('view_purchases');
        }
        $quantity = $purchase->getQuantityForSale();

        $em = $this->getDoctrine()->getManager();

        $user->setMoney($user->getMoney() + $quantity * $product->getPrice());

        $em->persist($user);
        $em->flush();

        $purchase->setQuantity($purchase->getQuantity() - $quantity);
        $purchase->setQuantityForSale($purchase->getQuantity());

        if($purchase->getQuantity() == 0){
            $em->remove($purchase);
            $em->flush();
        }else{
            $em->persist($purchase);
            $em->flush();
        }

        $this->addFlash('notice','You have successfully sold ' . strtoupper($product->getName()));
        return $this->redirectToRoute('home_index');


    }

    /**
     * @param Request $request
     * @Route("/purchases/sell/set-quantity", name="set_sell_quantity")
     * @Method({"POST"})
     * @return Response
     */
    public function setSellQuantity(Request $request)
    {
        if(!$this->getUser()){
            $this->addFlash('error','Log in in order to manage your purchases!');
            return $this->redirectToRoute('security_login');
        }
        $quantity = $request->request->get('quantity');
        $productId = $request->request->get('productId');
        $userId = $this->getUser()->getId();

        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->findOneByUserIdAndProductId($productId, $userId);

        if($purchase === null) {
            $this->addFlash('warning','You haven\'t bought this product!');
            return $this->redirectToRoute('view_purchases');
        }elseif($purchase->getQuantity() < $quantity) {
            $this->addFlash('warning','Cannot sell more than you have!');
            return $this->redirectToRoute('view_purchases');
        }elseif($quantity <= 0) {
            $this->addFlash('warning','Invalid Quantity For Sale!');
            return $this->redirectToRoute('view_purchases');
        }

        $em = $this->getDoctrine()->getManager();

        $purchase->setQuantityForSale($quantity);

        $em->persist($purchase);
        $em->flush();

        $this->addFlash('notice',"Quantity For Sale successfully set to ". strtoupper($quantity));
        return $this->redirectToRoute('view_purchases');


    }

}
