<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\UserType;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {
        /** Create user and form of the corresponding type in order later to be processed*/
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        /** Processing the form */
        $form->handleRequest($request);

        /** if the form is submitted - register the user otherwise render the register form */
        if($form->isSubmitted() && $form->isValid()){
            /** encode user's password and set it to the user we have instantiated*/
            $password = $this->get('security.password_encoder');
            $userPassword = $password->encodePassword($user,$user->getPassword());
            $user->setPassword($userPassword);

            /** save user to the database*/
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }
}
