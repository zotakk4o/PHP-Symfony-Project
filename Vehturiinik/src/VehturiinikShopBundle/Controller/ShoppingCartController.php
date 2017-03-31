<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\User;

class ShoppingCartController extends Controller
{
    /**
     * @Route("/cart", name="view_cart")
     */
    public function viewShoppingCartAction()
    {
        $userId = $this->getUser()->getId();

        $repository = $this->getDoctrine()->getManager()->getRepository(Product::class);

        /** Get the products the logged in user have */
        $query = $repository->createQueryBuilder('p')
            ->innerJoin('p.users', 'u')
            ->where('u.id = :user_id')
            ->setParameter('user_id', $userId)
            ->getQuery()->getResult();

        return $this->render('shopping/cart.html.twig', ['products' => $query]);
    }
}
