<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\Purchase;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\PurchaseQuantityType;

class ShopController extends Controller
{
    const PAGE_COUNT = 10;

    /**
     * @param Request $request
     * @Route("/shop", name="view_shop")
     * @return Response
     */
    public function viewCategoriesAction(Request $request)
   {
       $validCategories = [];
       $categories = $this->get('knp_paginator')->paginate(
           $this->getDoctrine()->getRepository(Category::class)->findAllAvailable(),
           $request->query->getInt('page',1),
            self::PAGE_COUNT
       );

       foreach ($categories->getItems() as $category){
           /**@var $category Category*/
           if($category->getProductsCount() > 0)$validCategories[] = $category;
       }
       $categories->setItems($validCategories);

       return $this->render('shop/categories.html.twig',['categories' => $categories]);
   }

    /**
     * @param $id
     * @param Request $request
     * @Route("shop/category/{id}", name="view_products_in_category")
     * @return Response
     */
    public function viewProductsInCategoryAction($id, Request $request)
   {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        if($category === null || !$category->isAvailable() || $category->getProductsCount() == 0){
            $this->addFlash('error','This category doesn\'t exist!');
            return $this->redirectToRoute('view_shop');
        }

        $products = $this->get('knp_paginator')->paginate(
            $category->getAllProducts(),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );

        if(empty($products->getItems())) {
            $this->addFlash('error', 'This Category is empty');
            return $this->redirectToRoute('view_shop');
        }

       return $this->render('shop/products.html.twig', ['products' => $products]);
   }

    /**
     * @param Request $request
     * @Route("/purchases", name="view_purchases")
     * @Security("has_role('ROLE_USER')")
     * @return Response
     */
    public function viewBoughtProductsAction(Request $request)
    {
        $userId = $this->getUser()->getId();

        $purchases = $this->get('knp_paginator')->paginate(
            $this->getDoctrine()->getRepository(Purchase::class)->findUserPurchases($userId),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );

        if(empty($purchases->getItems())){
            $this->addFlash('warning','You haven\'t bought any products!');
            return $this->redirectToRoute('view_shop');
        }
        $forms = [];
        foreach ($purchases as $purchase){
            /**@var Purchase $purchase*/
            $forms[$purchase->getProduct()->getName()] = $this->createForm(PurchaseQuantityType::class,
                $purchase,
                ['action' => $this->generateUrl('set_sell_quantity')])
                ->add('submit',SubmitType::class,['label' => 'Set Quantity','attr' => ['class' => 'btn-success']])
                ->createView();

        }

        return $this->render('shopping/bought.html.twig', ['purchases' => $purchases,'forms' => $forms]);
    }

    /**
     * @param $purchaseId int
     * @Route("/purchases/sell/purchase/{purchaseId}", name="sell_product")
     * @Security("has_role('ROLE_USER')")
     * @return Response
     */
    public function sellPurchaseAction($purchaseId)
    {
        /**@var User $user*/
        $user = $this->getUser();
        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->find($purchaseId);
        if($purchase === null || !$purchase->isAvailable()){
            $this->addFlash('warning','You haven\'t bought this product!');
            return $this->redirectToRoute('view_purchases');
        }

        $quantity = $purchase->getQuantityForSale();
        $em = $this->getDoctrine()->getManager();

        $user->setMoney($user->getMoney() + $quantity * ($purchase->getPricePerPiece() - ($purchase->getPricePerPiece() * $purchase->getDiscount() / 100)));

        $purchase->getProduct()->setQuantity($purchase->getQuantityForSale() + $purchase->getProduct()->getQuantity());
        $purchase->setCurrentQuantity($purchase->getCurrentQuantity() - $quantity);
        $purchase->setQuantityForSale($purchase->getCurrentQuantity());

        $em->persist($purchase);
        $em->flush();

        $this->addFlash('notice','You have successfully sold ' . strtoupper($purchase->getProduct()->getName()));
        return $this->redirectToRoute('home_index');
    }

    /**
     * @param Request $request
     * @Route("/purchases/sell/set-quantity", name="set_sell_quantity")
     * @Method({"POST"})
     * @Security("has_role('ROLE_USER')")
     * @return Response
     */
    public function setSellQuantity(Request $request)
    {
        $userId = $this->getUser()->getId();
        $params = $request->request->all()['purchase_quantity'];
        $quantity = $params['quantityForSale'];
        $productId = $params['productId'];
        $submittedToken = $params['_token'];

        $csrfToken = new CsrfToken('purchase_quantity', $submittedToken);

        if(!$this->get('security.csrf.token_manager')->isTokenValid($csrfToken)){
            $this->addFlash('error','Invalid CSRF Token!');
            return $this->redirectToRoute('home_index');
        }

        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->findOneByUserIdAndProductId($productId, $userId);

        if($purchase === null) {
            $this->addFlash('warning','You haven\'t bought this product!');
            return $this->redirectToRoute('view_purchases');
        }elseif($purchase->getCurrentQuantity() < $quantity) {
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
