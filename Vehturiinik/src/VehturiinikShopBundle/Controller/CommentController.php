<?php

namespace VehturiinikShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use VehturiinikShopBundle\Entity\Comment;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Form\CommentType;

/**
 * Class CommentController
 * @package VehturiinikShopBundle\Controller
 * @Route("/comments")
 */
class CommentController extends Controller
{
    const PAGE_COUNT = 5;
    /**
     * @param Request $request
     * @param int $id
     * @Route("/product/{id}", name="view_product_comments")
     * @return Response
     */
    public function viewCommentsAction(Request $request,int $id)
    {
        $productService = $this->get('app.product_service');
        $productService->clearInvalidDiscounts();

        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if($product === null || !$product->isAvailable()){
            $this->addFlash('warning','Product Not Found!');
            return $this->redirectToRoute('view_shop');
        }elseif(empty($product->getComments())){
            $this->addFlash('warning','This Product Has NO Comments!');
            return $this->redirectToRoute('view_shop');
        }

        $comments = $this->get('knp_paginator')->paginate(
            $product->getComments(),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );

        return $this->render('comments/productComments.html.twig',['comments' => $comments]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @Security("has_role('ROLE_USER')")
     * @Route("/product/{id}/add/comment", name="comment_product")
     * @return Response
     */
    public function addCommentAction(int $id, Request $request)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        if($product === null || !$product->isAvailable()){
            $this->addFlash('warning','Product Unavailable!');
            return $this->redirectToRoute('home_index');
        }
        $author = $this->getUser();
        $comment = new Comment();
        $comment->setAuthorId($author->getId());
        $comment->setProductId($product->getId());
        $comment->setAuthor($author);
        $comment->setProduct($product);

        $form = $this->createForm(CommentType::class,$comment)
            ->add('submit', SubmitType::class,['label'=>'Comment','attr'=>['class'=>'btn btn-primary']]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash('notice','You Have Successfully Commented This Product!');
            return $this->redirectToRoute('view_product_comments',['id'=>$id]);
        }
        return $this->render('comments/commentForm.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @Security("has_role('ROLE_USER')")
     * @Route("/product/edit/comment/{id}", name="edit_product_comment")
     * @return Response
     */
    public function editCommentAction(int $id, Request $request)
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->find($id);
        if($comment === null || $comment->getAuthorId() !== $this->getUser()->getId() || $comment->isDeleted()){
            $this->addFlash('warning','Comment Not Found Or Permission Denied!');
            return $this->redirectToRoute('view_shop');
        }

        $form = $this->createForm(CommentType::class,$comment)
            ->add('submit',SubmitType::class,['label'=>'Edit','attr'=>['class'=>'btn btn-primary']]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash('notice','Comment Edited Successfully!');
            return $this->redirectToRoute('view_product_comments',['id' => $comment->getProductId()]);
        }
        return $this->render('comments/commentForm.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @Security("has_role('ROLE_USER')")
     * @Route("/product/delete/comment/{id}", name="delete_product_comment")
     * @return Response
     */
    public function deleteCommentAction(int $id, Request $request)
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->find($id);
        if($comment === null || $comment->getAuthorId() !== $this->getUser()->getId() || $comment->isDeleted()){
            $this->addFlash('warning','Comment Not Found Or Permission Denied!');
            return $this->redirectToRoute('view_shop');
        }

        $comment->setDateDeleted(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('notice','You Have Successfully Removed Your Comment!');
        return $this->redirectToRoute('view_product_comments',['id' => $comment->getProductId()]);
    }
}
