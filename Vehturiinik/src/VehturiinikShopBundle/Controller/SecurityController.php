<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     */
    public function loginAction()
    {
        $productService = $this->get('app.product_service');
        $productService->clearInvalidDiscounts();

        if($this->getUser()){
            $this->addFlash('error','You are already logged in!');
            return $this->redirectToRoute('security_login');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        if($error !== null)$this->addFlash('error',$error->getMessage());

        return $this->render('security/login.html.twig');

    }

}
