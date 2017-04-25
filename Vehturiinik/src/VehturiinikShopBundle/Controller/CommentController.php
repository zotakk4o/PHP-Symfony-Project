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
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if($product === null || !$product->isAvailable()){
            $this->addFlash('warning','Product Not Found!');
            return $this->redirectToRoute('home_index');
        }elseif($product->getComments()->isEmpty()){
            $this->addFlash('warning','This Product Has NO Comments!');
            return $this->redirectToRoute('home_index');
        }

        $comments = $this->get('knp_paginator')->paginate(
            $product->getComments(),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );

        return $this->render('comments/productComments.html.twig',['comments' => $comments]);
    }
}
