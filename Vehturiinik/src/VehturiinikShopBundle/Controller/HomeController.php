<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use VehturiinikShopBundle\Entity\Product;

class HomeController extends Controller
{
    /**
     * @Route("/", name="home_index")
     */
    public function indexAction(Request $request)
    {

        $products = $this->getDoctrine()->getRepository(Product::class)->findFirstFive();

        return $this->render('home/index.html.twig',['products' => $products]);
    }
}
