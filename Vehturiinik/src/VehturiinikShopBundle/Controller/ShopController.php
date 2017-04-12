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

    /**
     * @Route("/shop", name="view_shop")
     */
    public function viewCategoriesAction()
   {
       $validCategories = [];
       $categories = $this->getDoctrine()->getRepository(Category::class)->findAllAvailable();

       foreach ($categories as $category){
           if($category->getProductsCount() > 0)$validCategories[] = $category;
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

        if($category === null || $category->getDateDeleted() !== null || $category->getProductsCount() == 0){
            $this->addFlash('error','This category doesn\'t exist!');
            return $this->redirectToRoute('view_shop');
        }

        $products = $category->getValidProducts();

        return $this->render('shop/products.html.twig', ['products' => $products]);
   }

    /**
     * @Route("/purchases", name="view_purchases")
     * @Security("has_role('ROLE_USER')")
     */
    public function viewBoughtProductsAction()
    {
        $userId = $this->getUser()->getId();

        $purchases = $this->getDoctrine()->getRepository(Purchase::class)->findBy(['userId' => $userId]);
        if(empty($purchases)){
            $this->addFlash('warning','You haven\'t bought any products!');
            return $this->redirectToRoute('view_shop');
        }
        $forms = [];
        foreach ($purchases as $purchase){
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

        $purchase->getProduct()->setQuantity($purchase->getQuantityForSale() + $purchase->getProduct()->getQuantity());
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
