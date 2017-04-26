<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\Purchase;
use VehturiinikShopBundle\Entity\Comment;
use VehturiinikShopBundle\Form\CommentType;
use Symfony\Component\HttpFoundation\Response;
use VehturiinikShopBundle\Entity\Role;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\UserRegisterType;

class UserController extends Controller
{
    const PAGE_COUNT = 10;
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {
        if($this->getUser()){
            $this->addFlash('warning','Logout in order to register again!');
            return $this->redirectToRoute('home_index');
        }
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user)
            ->add('submit',SubmitType::class, ['label' => 'Register','attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password = $this->get('security.password_encoder');
            $userPassword = $password->encodePassword($user,$user->getPassword());
            $user->setPassword($userPassword);

            $role = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);
            $user->addRole($role);
            $user->setMoney(4200);

            $em = $this->getDoctrine()->getManager();
            try{
                $em->persist($user);
                $em->flush();
            }catch(\Exception $e){
                $this->addFlash('error','Username Already Taken!');
                return $this->redirectToRoute('user_register');
            }

            $this->addFlash('notice','You have successfully registered to Vehturiinik!');
            return $this->redirectToRoute('security_login');
        }
        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param Request $request
     * @Route("/shop/cart", name="view_cart")
     * @Security("has_role('ROLE_USER')")
     * @return Response
     */
    public function viewCartAction(Request $request)
    {
        $session = $this->get('session');
        if(!$session->has('products') || empty($session->get('products'))){
            $this->addFlash('warning','You have no products in your cart!');
            return $this->redirectToRoute('view_shop');
        }

        /**@var Product[] $products*/
        $products = $this->get('knp_paginator')->paginate(
            $session->get('products'),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );
        $quantities = $session->get('quantities');

        if(in_array(0, $quantities)){
            $productName = array_search(0, $quantities);
            unset($quantities[$productName]);
            unset($products[$productName]);
            $session->set('products', $products);
            $session->set('quantities', $quantities);
        }

        $totalAmount = 0;
        foreach ($products as $product){
            $price = floatval($product->getPrice()) * $quantities[$product->getName()];
            if($product->getDiscount() == 0 && $this->getUser()->isRegularCustomer() || $product->getDiscount() == 0 && $this->getUser()->getMoney() >= User::MONEY_TO_BE_DISCOUNTED)
                $totalAmount += $price - ($price * User::USER_DISCOUNT / 100);
            else $totalAmount += $price;
        }

        $session->set('total', $totalAmount);
        return $this->render('shopping/cart.html.twig', ['products' => $products,'quantities' => $quantities,'total'=>$totalAmount]);
    }


    /**
     * @param $id int
     * @Route("/shop/add-to-cart/{id}",name="add_product")
     * @Security("has_role('ROLE_USER')")
     * @return Response
     */
    public function addToCartAction($id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        if($product === null || !$product->getCategory()->isAvailable()){
            $this->addFlash('error','This product doesn\'t exist');
            return $this->redirectToRoute('view_shop');
        }

        $session = $this->get('session');
        if(!$session->has('products') && !$session->has('quantities')){
            $session->set('products', []);
            $session->set('quantities',[]);
        }

        $products = $session->get('products');
        $quantities = $session->get('quantities');

        if(!array_key_exists($product->getName(),$products)){
            $products[$product->getName()] = $product;
            $quantities[$product->getName()] = 1;
        }else{
            $quantities[$product->getName()]++;
        }

        $session->set('products', $products);
        $session->set('quantities', $quantities);

        $this->addFlash('notice', strtoupper($product->getName()) . " successfully added to your cart!");
        return $this->redirectToRoute('view_products_in_category',['id' => $product->getCategory()->getId()]);
    }

    /**
     * @param $productName
     * @Route("/shop/remove-from-cart/{productName}", name="remove_all_from_cart")
     * @Security("has_role('ROLE_USER')")
     * @return RedirectResponse
     */
    public function removeProductFromCartAction($productName)
    {
        $session = $this->get('session');
        $products = $session->get('products');
        if(!array_key_exists($productName, $products)){
            $this->addFlash('error','Product not in cart!');
            return $this->redirectToRoute('home_index');
        }
        $quantities = $session->get('quantities');
        unset($products[$productName]);
        unset($quantities[$productName]);
        $session->set('products',$products);
        $session->set('quantities',$quantities);


        $this->addFlash('notice','You have successfully removed '. strtoupper($productName) .' product from your cart!');

        return $this->redirectToRoute('view_cart');
    }

    /**
     * @param Request $request
     * @Route("/shop/add-quantity", name="set_quantity")
     * @Method({"POST"})
     * @Security("has_role('ROLE_USER')")
     * @return RedirectResponse
     */
    public function setQuantityAction(Request $request)
    {
        $productName = $request->request->get('productName');
        $quantity = $request->request->get('quantity');
        $submittedToken = $request->request->get('_csrf_token');

        $csrfToken = new CsrfToken('quantity_form', $submittedToken);
        if(!$this->get('security.csrf.token_manager')->isTokenValid($csrfToken)){
            $this->addFlash('error','Invalid CSRF Token!');
            return $this->redirectToRoute('home_index');
        }

        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['name' => $productName]);
        if($product === null || !$product->isAvailable() || !$product->getCategory()->isAvailable()){
            $this->addFlash('warning','Invalid Product!');
            return $this->redirectToRoute('view_cart');
        }elseif ($quantity <= 0 || $product->getQuantity() < $quantity){
            $this->addFlash('warning','Invalid Quantity!');
            return $this->redirectToRoute('view_cart');
        }

        $session = $this->get('session');
        $quantities = $session->get('quantities');
        $quantities[$productName] = $quantity;
        $session->set('quantities',$quantities);

        $this->addFlash('notice',"Quantity successfully set to ". strtoupper($quantity));
        return $this->redirectToRoute('view_cart');
    }

    /**
     * @Route("/shop/cart/clear", name="clear_cart")
     * @Security("has_role('ROLE_USER')")
     * @return RedirectResponse
     */
    public function clearCartAction()
    {
        $session = $this->get('session');

        if(!$session->has('products') || empty($session->get('products'))){
            $this->addFlash('warning','You have no items in your cart!');
            return $this->redirectToRoute('view_shop');
        }

        $session->remove('products');
        $session->remove('quantities');

        $this->addFlash('notice','Cart was successfully cleared!');

        return $this->redirectToRoute('home_index');

    }

    /**
     * @Route("/shop/cart/checkout", name="checkout_cart")
     * @Security("has_role('ROLE_USER')")
     */
    public function checkOutCartAction()
    {
        $session = $this->get('session');
        if(!$session->has('products')){
            $this->addFlash('warning','You Have No Items In Your Cart!');
            return $this->redirectToRoute('view_shop');
        }
        if($session->get('total') > $this->getUser()->getMoney()){
            $this->addFlash('warning','Not Enough Money In The Pocket!');
            return $this->redirectToRoute('view_cart');
        }

        $em = $this->getDoctrine()->getManager();
        $products = $session->get('products');
        $quantities = $session->get('quantities');

        /**@var Product[] $products*/
        foreach ($products as $product){
            $this->buyProduct($product->getName(), $em, $session, $products, $quantities);
        }

        $this->getUser()->setMoney($this->getUser()->getMoney() - $this->get('session')->get('total'));
        $em->flush();

        $this->addFlash('notice','You have successfully made your purchase');
        return $this->redirectToRoute('view_purchases');
    }

    private function buyProduct(string $productName,ObjectManager $em, SessionInterface &$session, array &$products, array &$quantities)
    {
        $user = $this->getUser();
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['name'=>$productName]);

        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->findOneByUserIdAndProductId($product->getId(), $user->getId());
        $product->setQuantity($product->getQuantity() - $quantities[$productName]);

        if($purchase !== null){
            $purchase->setQuantityBought($purchase->getCurrentQuantity() + $quantities[$productName])
                ->setCurrentQuantity($purchase->getCurrentQuantity() + $quantities[$productName])
                ->setQuantityForSale($purchase->getCurrentQuantity())
                ->setPricePerPiece($product->getOriginalPrice())
                ->setDateDeleted(null)
                ->setDatePurchased(new \DateTime('now'));
        }else{
            $purchase = new Purchase();
            $purchase->setProduct($product)->setUser($user)->setCurrentQuantity($quantities[$productName])->setQuantityForSale($quantities[$productName])->setQuantityBought($quantities[$productName])->setPricePerPiece($product->getOriginalPrice());
            $user->addPurchase($purchase);
        }

        if($product->getDiscount() !== 0)$purchase->setDiscount($product->getDiscount());
        elseif($user->isRegularCustomer() || $user->getMoney() >= User::MONEY_TO_BE_DISCOUNTED)$purchase->setDiscount(User::USER_DISCOUNT);
        else $purchase->setDiscount(0);

        $em->persist($purchase);
        $em->flush();

        unset($products[$product->getName()]);
        unset($quantities[$product->getName()]);
        $session->set('products',$products);
        $session->set('quantities',$quantities);
    }
}
