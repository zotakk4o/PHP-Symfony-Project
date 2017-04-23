<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VehturiinikShopBundle\Entity\Role;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\RegisterType;

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
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user)
            ->add('submit',SubmitType::class, ['label' => 'Register','attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password = $this->get('security.password_encoder');
            $userPassword = $password->encodePassword($user,$user->getPassword());
            $user->setPassword($userPassword);

            $role = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);
            $user->addRole($role);
            $user->setMoney(4200);

            $em = $this->getDoctrine()->getManager();
            try{
                $em->persist($user);
                $em->flush();
            }catch(\Exception $e){
                $this->addFlash('error','Username Already Taken!');
                return $this->redirectToRoute('user_register');
            }

            $this->addFlash('notice','You have successfully registered to Vehturiinik!');
            return $this->redirectToRoute('security_login');
        }
        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }
}
