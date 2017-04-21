<?php

namespace VehturiinikShopBundle\Controller\Administration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\Purchase;
use VehturiinikShopBundle\Entity\Role;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\PurchaseType;
use VehturiinikShopBundle\Form\UserType;

/**
 * Class UserController
 * @package VehturiinikShopBundle\Controller\Administration
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("/administration")
 */
class UserController extends Controller
{

    /**
     * @param Request $request
     * @Route("/users", name="view_users_panel")
     * @return Response
     */
    public function viewUsersAction(Request $request)
    {

        $users = $this->get('knp_paginator')->paginate(
            $this->getDoctrine()->getRepository(User::class)->findAll(),
            $request->query->getInt('page',1),
            10
        );

        return $this->render('administration/users/users.html.twig',['users' => $users]);

    }

    /**
     * @param int $id
     * @param Request $request
     * @Route("/user/{id}", name="edit_user")
     * @return Response
     */
    public function editUserAction(int $id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        if($user === null){
            $this->addFlash('error','This User Doesn\'t Exist!');
            return $this->redirectToRoute('view_users_panel');
        }

        $form = $this->createForm(UserType::class, $user);
        if($request->isMethod('POST')){
            $this->validateUserForm($request, $form);
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
        }
        return $this->render('administration/users/user.html.twig',['user' => $user, 'form' => $form->createView()]);
    }

    /**
     * @param int $id
     * @Route("/user/{id}/purchases", name="view_user_purchases")
     * @return Response
     */
    public function viewUserPurchasesAction(int $id)
    {
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

        return $this->render('administration/users/purchases.html.twig',['purchases' => $purchases]);

    }

    /**
     * @param int $userId
     * @param int $purchaseId
     * @Route("/user/{userId}/purchase/{purchaseId}", name="remove_purchase")
     * @return Response
     */
    public function removePurchaseAction($userId, $purchaseId)
    {
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
     * @Route("/user/{userId}/purchase/edit/{purchaseId}", name="edit_purchase")
     * @return Response
     */
    public function editUserPurchaseAction(Request $request,$userId, $purchaseId)
    {
        $purchase = $this->getDoctrine()->getRepository(Purchase::class)->find($purchaseId);

        if($purchase === null || $purchase->getUserId() != $userId){
            $this->addFlash('warning','This User Hasn\'t Bought This Product!');
            return $this->redirectToRoute('view_user_purchases',['id' => $userId]);
        }

        $form = $this->createForm(PurchaseType::class, $purchase)
            ->add('submit',
                SubmitType::class,array(
                    'label' => 'Edit Purchase',
                    'attr' => ['class' => 'btn btn-primary']
                ));

        if($request->isMethod('POST')){
            $this->validatePurchaseForm($request, $form, $purchase);
            if($form->isSubmitted() && $form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($purchase);
                $em->flush();

                $this->addFlash('notice','Purchase Successfully Edited!');
                return $this->redirectToRoute('view_user_purchases',['id' => $userId]);
            }
        }
        return $this->render('administration/users/purchase.html.twig',['purchase' => $purchase, 'form' => $form->createView()]);

    }

    private function validateUserForm(Request $request, FormInterface $form)
    {
        $requestParams = $request->request->all()['user'];
        if($requestParams['username'] === '' || $requestParams['fullName'] === ''){
            $form->addError(new FormError('Username and Full Name Cannot be Empty!'));
        }
        if(array_key_exists('roles',$requestParams))
        {
            if(count($requestParams['roles']) == 1)
            {
                if($requestParams['roles'][0] !== 'ROLE_ADMIN' && $requestParams['roles'][0] !== 'ROLE_EDITOR'){
                    $form->addError(new FormError('Invalid User Roles!'));
                }
                else
                {
                    $form->submit($request->request->get($form->getName()));
                }
            }elseif(count($requestParams['roles']) == 2)
            {
                if(
                    $requestParams['roles'][0] !== 'ROLE_ADMIN'
                    && $requestParams['roles'][0] !== 'ROLE_EDITOR'
                    || $requestParams['roles'][1] !== 'ROLE_EDITOR'
                    && $requestParams['roles'][1] !== 'ROLE_ADMIN')
                {
                    $form->addError(new FormError('Invalid User Roles!'));
                }
                else
                {
                    $form->submit($request->request->get($form->getName()));
                }
            }
            elseif(count($requestParams['roles']) > 2)
            {
                $form->addError(new FormError('Invalid User Roles!'));
            }
        }
        else
        {
            $form->submit($request->request->get($form->getName()));
        }
    }

    private function validatePurchaseForm(Request $request, FormInterface $form, Purchase $purchase)
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $userIds = [];
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        $productIds = [];

        foreach ($users as $user){
            $userIds[$user->getFullName()] = $user->getId();
        }
        foreach($products as $product)
        {
            $productIds[$product->getName()] = $product->getId();
        }
        $requestParams = $request->request->all()['purchase'];
        if($requestParams['quantity'] === ''
            || $requestParams['quantityForSale'] === ''
            || $requestParams['userId'] === ''
            || $requestParams['productId'] === '')
        {
            $form->addError(new FormError('Form Data Cannot be Empty!'));
        }
        elseif(intval($requestParams['quantity']) > $purchase->getProduct()->getQuantity()
            || intval($requestParams['quantityForSale']) > $purchase->getProduct()->getQuantity()
            || $requestParams['quantity'] <= 0
            || $requestParams['quantityForSale'] <= 0
            || !is_numeric($requestParams['quantityForSale'])
            || !is_numeric($requestParams['quantity']))
        {
            $form->addError(new FormError('Invalid Quantity or Quantity For Sale!'));
        }
        elseif($requestParams['discount'] < 0 || $requestParams['discount'] >= 99)
        {
            $form->addError(new FormError('Discount Should be Between 0% and 99%!'));
        }
        elseif(!in_array($requestParams['userId'],$userIds) || !in_array($requestParams['productId'],$productIds))
        {
            $form->addError(new FormError('Invalid UserId or ProductId!'));
        }
        else
        {
            $form->submit($request->request->get($form->getName()));
        }
    }

}
