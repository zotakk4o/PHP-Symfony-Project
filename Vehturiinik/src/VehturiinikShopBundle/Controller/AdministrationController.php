<?php

namespace VehturiinikShopBundle\Controller;

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
    /**
     * @Route("/administration/users", name="view_users_panel")
     * @return Response
     */
    public function viewUsersAction()
    {
        if(!$this->authenticate()){
            $this->addFlash('error','Access Denied!');
            return $this->redirectToRoute('home_index');
        }

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('administration/users.html.twig',['users' => $users]);

    }

    /**
     * @param int $id
     * @param Request $request
     * @Route("/administration/user/{id}", name="edit_user")
     * @return Response
     */
    public function editUserAction(int $id, Request $request)
    {
        if(!$this->authenticate()){
            $this->addFlash('error','Access Denied!');
            return $this->redirectToRoute('home_index');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $roles = [];
            foreach ($form->getData()->getRoles() as $roleName)$roles[] = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => $roleName]);
            $user->setRoles($roles);
            $user->addRole($this->getDoctrine()->getRepository(Role::class)->findOneBy(['name'=>'ROLE_USER']));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('notice','User Successfully Updated!');
            return $this->redirectToRoute('view_users_panel');
        }


        return $this->render('administration/user.html.twig',['user' => $user, 'form' => $form->createView()]);

    }

    private function authenticate()
    {
        return $this->getUser() && $this->getUser()->isAdmin();
    }
}
