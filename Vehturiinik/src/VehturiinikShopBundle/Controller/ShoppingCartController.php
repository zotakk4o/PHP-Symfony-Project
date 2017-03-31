<?php

namespace VehturiinikShopBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\User;

class ShoppingCartController extends Controller
{
    /**
     * @Route("/purchases", name="view_purchases")
     */
    public function viewBoughtProductsAction()
    {
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
        /**
         * Retrieve the logged in user in order later to add the product to his purchases list
         *
         * @var $user User
         */
        $user = $this->getUser();

        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if($product === null){
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
        $session = $this->get('session');

        if(!$session->has('products')){
            return $this->redirectToRoute('view_shop');
        }

        $products = $this->get('session')->get('products');

        return $this->render('shopping/bought.html.twig', ['products' => $products]);
    }


    /**
     * @param $id int
     * @Route("/shop/add-to-cart/{id}",name="add_product")
     * @return Response
     */
    public function addToCartAction($id)
    {

        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        $productName = $product->getName();
        if($product === null){
            $this->redirectToRoute('view_shop');
        }

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


        return $this->render('shopping/cart.html.twig', ['products' => $products,'session' => $session]);


    }
}
