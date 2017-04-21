<?php

namespace VehturiinikShopBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\Purchase;
use VehturiinikShopBundle\Entity\User;

class ShoppingCartController extends Controller
{
    /**
     * @Route("/shop/cart", name="view_cart")
     * @Security("has_role('ROLE_USER')")
     * @return Response
     */
    public function viewCartAction()
    {
        $session = $this->get('session');

        if(!$session->has('products') || empty($session->get('products'))){
            $this->addFlash('warning','You have no products in your cart!');
            return $this->redirectToRoute('view_shop');
        }

        /**@var Product[] $products*/
        $products = $session->get('products');
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
            $totalAmount += floatval($product->getPrice()) * $quantities[$product->getName()];
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

        $productName = $product->getName();
        if($product === null || $product->getCategory()->getDateDeleted() !== null){
            $this->addFlash('error','This product doesn\'t exist');
            return $this->redirectToRoute('view_shop');
        }

        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $product->getCategoryId()]);
        $product->setCategory($category);

        $session = $this->get('session');

        if(!$session->has('products') && !$session->has('quantities')){
            $session->set('products', []);
            $session->set('quantities',[]);
        }

        $products = $session->get('products');
        $quantities = $session->get('quantities');

        if(!array_key_exists($productName,$products)){
            $products[$productName] = $product;
            $quantities[$productName] = 1;
        }else{
            $quantities[$productName]++;
        }
        $session->set('products', $products);
        $session->set('quantities', $quantities);

        $this->addFlash('notice', strtoupper($productName) . " successfully added to your cart!");

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

        if($product === null || $product->getDateDeleted() !== null){
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

        if(!$session->has('products')){
            $this->addFlash('warning','You have no items in your cart!');
            return $this->redirectToRoute('home_index');
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
        $user = $this->getUser();

        $session = $this->get('session');
        $total = $session->get('total');

        if(!$session->has('products')){
            $this->addFlash('warning','You Have No Items In Your Cart!');
            return $this->redirectToRoute('view_shop');
        }
        if($total > $user->getMoney()){
            $this->addFlash('warning','Not Enough Money In The Pocket!');
            return $this->redirectToRoute('view_cart');
        }

        $quantities = $session->get('quantities');
        $products = $session->get('products');

        $productNames = array_keys($quantities);


        $em = $this->getDoctrine()->getManager();

        foreach ($productNames as $productName){
            $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['name'=>$productName]);
            $quantity = $quantities[$product->getName()];
            $productPrice = $product->getPrice();

            $purchase = $this->getDoctrine()->getRepository(Purchase::class)->findOneByUserIdAndProductId($product->getId(), $user->getId());

            $user->setMoney($user->getMoney() - $productPrice * $quantity);
            $product->setQuantity($product->getQuantity() - $quantity);
            if($purchase !== null){
                $purchase
                    ->setQuantity($purchase->getQuantity() + $quantity)
                    ->setQuantityForSale($purchase->getQuantity());
            }else{
                $purchase = new Purchase();
                $purchase
                    ->setProduct($product)
                    ->setUser($user)
                    ->setQuantity($quantity)
                    ->setQuantityForSale($quantity);
                $user->addPurchase($purchase);
            }

            if($product->getDiscount() !== 0)$purchase->setDiscount($product->getDiscount());
            else $purchase->setDiscount(0);

            $em->persist($purchase);
            $em->flush();

            unset($products[$product->getName()]);
            unset($quantities[$product->getName()]);
            $session->set('products',$products);
            $session->set('quantities',$quantities);

        }

        $this->addFlash('notice','You have successfully made your purchase');
        return $this->redirectToRoute('view_purchases');

    }

}
