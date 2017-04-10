<?php

namespace VehturiinikShopBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use VehturiinikShopBundle\Entity\Purchase;
use VehturiinikShopBundle\Entity\Role;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\PurchaseType;
use VehturiinikShopBundle\Form\UserType;

class EditUserController extends Controller
{

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
        if($user === null){
            $this->addFlash('error','This User Doesn\'t Exist!');
            return $this->redirectToRoute('view_users_panel');
        }

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

    /**
     * @param int $id
     * @Route("/administration/user/{id}/purchases", name="view_user_purchases")
     * @return Response
     */
    public function viewUserPurchasesAction(int $id)
    {
        if(!$this->authenticate()){
            $this->addFlash('error','Access Denied!');
            return $this->redirectToRoute('home_index');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        if($user === null){
            $this->addFlash('error','This User Doesn\'t Exist!');
            return $this->redirectToRoute('view_users_panel');
        }
        $purchases = $user->getPurchases();

        if($purchases[0] === null){
            $this->addFlash('warning','This User hasn\'t Bought Any Products!');
            return $this->redirectToRoute('view_users_panel');
        }

        return $this->render('administration/purchases.html.twig',['purchases' => $purchases]);

    }

    /**
     * @param int $userId
     * @param int $purchaseId
     * @Route("/administration/user/{userId}/purchase/{purchaseId}", name="remove_purchase")
     * @return Response
     */
    public function removePurchaseAction($userId, $purchaseId)
    {
        if(!$this->authenticate()){
            $this->addFlash('error','Access Denied!');
            return $this->redirectToRoute('home_index');
        }
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->find($purchaseId);
        $product = $purchase->getProduct();

        if($purchase === null || $purchase->getUserId() != $userId){
            $this->addFlash('warning','This User Hasn\'t Bought This Product!');
            return $this->redirectToRoute('view_user_purchases',['id' => $userId]);
        }

        $user->setMoney($user->getMoney() + $product->getPrice() * $purchase->getQuantity());
        $product->setQuantity($product->getQuantity() + $purchase->getQuantity());

        $em = $this->getDoctrine()->getManager();
        $em->remove($purchase);
        $em->flush();

        $this->addFlash('notice','Purchase Successfully Removed!');
        return $this->redirectToRoute('view_users_panel');


    }

    /**
     * @param Request $request
     * @param $userId
     * @param $purchaseId
     *
     * @Route("/administration/user/{userId}/purchase/edit/{purchaseId}", name="edit_purchase")
     * @return Response
     */
    public function editUserPurchaseAction(Request $request,$userId, $purchaseId)
    {
        if(!$this->authenticate()){
            $this->addFlash('error','Access Denied!');
            return $this->redirectToRoute('home_index');
        }

        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->find($purchaseId);

        if($purchase === null || $purchase->getUserId() != $userId){
            $this->addFlash('warning','This User Hasn\'t Bought This Product!');
            return $this->redirectToRoute('view_user_purchases',['id' => $userId]);
        }

        $form = $this->createForm(PurchaseType::class, $purchase)
            ->add('submit',SubmitType::class,array('label' => 'Edit Purchase','attr' => ['class' => 'btn btn-primary']));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($purchase);
            $em->flush();

            $this->addFlash('notice','Purchase Successfully Edited!');
            return $this->redirectToRoute('view_user_purchases',['id' => $userId]);
        }

        return $this->render('administration/purchase.html.twig',['purchase' => $purchase, 'form' => $form->createView()]);

    }
    private function authenticate()
    {
        return $this->getUser() && $this->getUser()->isAdmin();
    }

}
