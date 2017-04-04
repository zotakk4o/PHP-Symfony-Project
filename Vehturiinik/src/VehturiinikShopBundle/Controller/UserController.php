<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VehturiinikShopBundle\Entity\Role;
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
        if($this->getUser()){
            $this->addFlash('warning','Logout in order to register again!');
            return $this->redirectToRoute('home_index');
        }
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

            /**Retrieve default role from database and add it to the user*/
            $role = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);
            $user->addRole($role);

            /**Give user some money to spend later on our vehturiiki*/
            $user->setMoney(4200);

            /** save user to the database*/
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('notice','You have successfully registered to Vehturiinik!');
            return $this->redirectToRoute('security_login');
        }

        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }
}
