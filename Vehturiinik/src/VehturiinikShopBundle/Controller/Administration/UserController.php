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
use VehturiinikShopBundle\Entity\Comment;
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
    const PAGE_COUNT = 15;

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
            self::PAGE_COUNT
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
        return $this->render('administration/users/user.html.twig',['user' => $user, 'form' => $form->createView()]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @Route("/user/{id}/purchases", name="view_user_purchases")
     * @return Response
     */
    public function viewUserPurchasesAction(int $id, Request $request)
    {
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        if($user === null){
            $this->addFlash('error','This User Doesn\'t Exist!');
            return $this->redirectToRoute('view_users_panel');
        }
        $purchases = $this->get('knp_paginator')->paginate(
            $user->getPurchases(),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );

        if(empty($purchases->getItems())){
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

        if($purchase === null || $purchase->getUserId() != $userId || !$purchase->isAvailable()){
            $this->addFlash('warning','This User Hasn\'t Bought This Product!');
            return $this->redirectToRoute('view_user_purchases',['id' => $userId]);
        }

        $user->setMoney($user->getMoney() + ($purchase->getPricePerPiece() - ($purchase->getPricePerPiece() * $purchase->getDiscount() / 100)) * $purchase->getCurrentQuantity());
        $product->setQuantity($product->getQuantity() + $purchase->getCurrentQuantity());
        $purchase->setDateDeleted(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
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
        $userIds = [];
        $productIds = [];
        foreach ($this->getDoctrine()->getRepository(User::class)->findAll() as $user)
            $userIds[] = $user->getId();
        foreach($this->getDoctrine()->getRepository(Product::class)->findAll() as $product)
            $productIds[] = $product->getId();

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

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if(!in_array($form->getData()->getUserId(),$userIds) || !in_array($form->getData()->getProductId(), $productIds)){
                $this->addFlash('warning','Invalid User Id or Product Id!');
                return $this->render('administration/users/purchase.html.twig',['purchase' => $purchase, 'form' => $form->createView()]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($purchase);
            $em->flush();

            $this->addFlash('notice','Purchase Successfully Edited!');
            return $this->redirectToRoute('view_user_purchases',['id' => $userId]);
        }

        return $this->render('administration/users/purchase.html.twig',['purchase' => $purchase, 'form' => $form->createView()]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @Route("/user/{id}/comments", name="view_user_comments")
     * @return Response
     */
    public function viewUserCommentsAction(int $id, Request $request)
    {
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        if($user === null){
            $this->addFlash('error','This User Doesn\'t Exist!');
            return $this->redirectToRoute('view_users_panel');
        }

        $comments = $this->get('knp_paginator')->paginate(
            $user->getComments(),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );

        if(empty($comments->getItems())){
            $this->addFlash('warning','This User hasn\'t Commented Any Products!');
            return $this->redirectToRoute('view_users_panel');
        }

        return $this->render('administration/users/comments.html.twig',['comments' => $comments]);
    }

    /**
     * @param int $userId
     * @param int $commentId
     * @Route("/user/{userId}/comment/{commentId}", name="edit_user_comment")
     * @return Response
     */
    public function removeUserCommentAction(int $userId, int $commentId)
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->find($commentId);
        if($comment === null || $comment->getAuthorId() != $userId || $comment->isDeleted()){
            $this->addFlash('warning','This User Hasn\'t Made This Comment!');
            return $this->redirectToRoute('view_user_comments',['id' => $userId]);
        }

        $comment->setDateDeleted(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('notice','Comment Successfully Removed!');
        return $this->redirectToRoute('view_user_comments',['id' => $userId]);
    }
}
