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
    public function login()
    {
        if($this->getUser()){
            $this->addFlash('error','You are already logged in!');
            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/login.html.twig');
    }

}
