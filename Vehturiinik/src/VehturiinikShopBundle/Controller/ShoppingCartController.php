<?php

namespace VehturiinikShopBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\User;

class ShoppingCartController extends Controller
{
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

        $repository = $this->getDoctrine()->getManager()->getRepository(Product::class);

        /** Get the products the logged in user have */
        $query = $repository->createQueryBuilder('p')
            ->innerJoin('p.users', 'u')
            ->where('u.id = :user_id')
            ->setParameter('user_id', $userId)
            ->getQuery()->getResult();

        return $this->render('shopping/bought.html.twig', ['products' => $query]);
    }

    /**
     * @param $id int
     * @Route("/shop/buy/{id}",name="buy_product")
     * @return RedirectResponse
     */
    public function buyProductAction($id)
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

        $em = $this->getDoctrine()->getManager();
        $user->addProduct($product);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('view_purchases');
    }

    /**
     * @Route("/shop/cart", name="view_cart")
     * @return Response
     */
    public function viewCartAction()
    {
        if(!$this->getUser()){
            $this->addFlash('error','Log in in order to access your cart!');
            return $this->redirectToRoute('security_login');
        }
        $session = $this->get('session');

        if(!$session->has('products') || empty($session->get('products'))){
            $this->addFlash('warning','You have no products in your cart!');
            return $this->redirectToRoute('view_shop');
        }


        $products = $session->get('products');

        if(in_array(0, $session->all())){
            $productName = array_search(0, $session->all());
            $session->remove($productName);
            $products = $session->get('products');
            unset($products[$productName]);
            $session->set('products', $products);
        }

        return $this->render('shopping/cart.html.twig', ['products' => $products]);
    }


    /**
     * @param $id int
     * @Route("/shop/add-to-cart/{id}",name="add_product")
     * @return Response
     */
    public function addToCartAction($id)
    {
        if(!$this->getUser()){
            $this->addFlash('error','Log in in order to add products to your cart!');
            return $this->redirectToRoute('security_login');
        }

        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        $productName = $product->getName();
        if($product === null){
            $this->addFlash('error','This product doesn\'t exist');
            $this->redirectToRoute('view_shop');
        }

        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $product->getCategoryId()]);
        $product->setCategory($category);

        $session = $this->get('session');

        if(!$session->has('products')){
            $session->set('products', []);
        }

        $products = $session->get('products');
        $productCount = $session->get($productName);

        if(!array_key_exists($productName,$products)){
            $products[$productName] = $product;
            $session->set($productName,1);
        }else{
            $session->set($productName, $productCount+1);
        }
        $session->set('products', $products);

        $this->addFlash('notice', strtoupper($productName) . " successfully added to your cart!");

        return $this->redirectToRoute('view_products_in_category',['id' => $product->getCategory()->getId()]);


    }

    /**
     * @param $productName
     * @Route("/shop/remove-from-cart/{productName}", name="remove_all_from_cart")
     * @return RedirectResponse
     */
    public function removeProductFromCartAction($productName)
    {
        if(!$this->getUser()){
            $this->addFlash('error','Log in in order to access your cart!');
            return $this->redirectToRoute('security_login');
        }

        $session = $this->get('session');
        $session->remove($productName);
        $products = $session->get('products');
        unset($products[$productName]);
        $session->set('products',$products);


        $this->addFlash('notice','You have successfully removed '. strtoupper($productName) .' product from your cart!');

        return $this->redirectToRoute('view_cart');
    }

    /**
     * @param Request $request
     * @Route("/shop/add-quantity", name="add_quantity")
     * @Method({"POST"})
     * @return RedirectResponse
     */
    public function addQuantityAction(Request $request)
    {
        if(!$this->getUser()){
            $this->addFlash('error','Log in in order to manage to your cart!');
            return $this->redirectToRoute('security_login');
        }
        $productName = $request->request->get('productName');
        $quantity = $request->request->get('quantity');

        $session = $this->get('session');

        if(!$session->has($productName)){
            return $this->redirectToRoute('view_cart');
        }

        $session->set($productName, $quantity);

        return $this->redirectToRoute('view_cart');
    }
}
