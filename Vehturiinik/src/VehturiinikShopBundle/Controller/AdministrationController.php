<?php

namespace VehturiinikShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use VehturiinikShopBundle\Entity\User;

class AdministrationController extends Controller
{
    /**
     * @Route("/administration", name="view_admin_panel")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /**@var $user User*/
        $user = $this->getUser();
        if(!$user){
            $this->addFlash('error','Log in order to access this page!');
            return $this->redirectToRoute('security_login');
        }elseif(!$user->isAdmin() ) {
            $this->addFlash('error','Access Denied!');
            return $this->redirectToRoute('home_index');
        }

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('administration/users.html.twig',['users' => $users]);

    }
}
