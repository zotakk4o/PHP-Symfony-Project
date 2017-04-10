<?php

namespace VehturiinikShopBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use VehturiinikShopBundle\Entity\Role;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\UserType;

class AdministrationController extends Controller
{
    /**
     * @Route("/administration",name="view_admin_panel")
     */
    public function indexAction()
    {
        if(!$this->authenticate()){
            $this->addFlash('error','Access Denied!');
            return $this->redirectToRoute('home_index');
        }

        return $this->render('administration/panel.html.twig');
    }

    private function authenticate()
    {
        return $this->getUser() && $this->getUser()->isAdmin() || $this->getUser() && $this->getUser()->isEditor();
    }
}
