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
        /** Create user and form of the corresponding type in order later to be processed*/
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user)
            ->add('submit',SubmitType::class, ['label' => 'Register','attr' => ['class' => 'btn btn-primary']]);

        /** Processing the form */
        if($request->isMethod('POST')){
            $this->validateUserForm($request, $form);
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
        }
        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }

    private function validateUserForm(Request $request, FormInterface $form)
    {
        $requestParams = $request->request->all()['register'];
        if($requestParams['username'] === '' || $requestParams['fullName'] === '' || $requestParams['password'] === ''){
            $form->addError(new FormError('Form Data Cannot be Empty!'));
        }
        else
        {
            $form->submit($request->request->get($form->getName()));
        }
    }
}
