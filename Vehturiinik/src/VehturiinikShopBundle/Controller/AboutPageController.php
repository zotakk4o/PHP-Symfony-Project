<?php

namespace VehturiinikShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class AboutPageController extends Controller
{
    /**
     * @Route("/about", name="view_about_page")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $productService = $this->get('app.product_service');
        $productService->clearInvalidDiscounts();

        return $this->render('about/about.html.twig');
    }
}
