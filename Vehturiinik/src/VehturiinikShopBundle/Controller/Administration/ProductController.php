<?php

namespace VehturiinikShopBundle\Controller\Administration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Entity\Comment;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\DiscountType;
use VehturiinikShopBundle\Form\ProductType;
use VehturiinikShopBundle\Form\AdminCommentType;

/**
 * Class ProductController
 * @package VehturiinikShopBundle\Controller
 * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_EDITOR')")
 * @Route("/administration/products")
 */
class ProductController extends Controller
{
    const PAGE_COUNT = 10;
    /**
     * @param Request $request
     * @Route("/", name="view_products_in_categories_panel")
     * @return Response
     */
    public function viewProductsInCategories(Request $request)
    {
        $categories = $this->get('knp_paginator')->paginate(
            $this->getDoctrine()->getRepository(Category::class)->findAllAvailable(),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );

        if(empty($categories->getItems())){
            $this->addFlash('warning','No Categories Found');
            return $this->redirectToRoute('add_category');
        }

        return $this->render('administration/products/categories.html.twig',['categories' => $categories]);
    }

    /**
     * @param $id
     * @param Request $request
     * @Route("/category/{id}", name="view_products_panel")
     * @return Response
     */
    public function viewProductsInCategoryAction($id, Request $request)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        if($category === null || !$category->isAvailable()){
            $this->addFlash('error','This Category Doesn\'t Exist!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        $products = $this->get('knp_paginator')->paginate(
            $category->getProducts(),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );
        if(empty($products->getItems())){
            $this->addFlash('error','This Category is empty');
            return $this->redirectToRoute('add_product_admin',['id' => $id]);
        }

        return $this->render('administration/products/products.html.twig', ['products' => $products,'categoryId' => $id]);
    }

    /**
     * @param Request $request
     * @param $id
     * @Route("/remove/{id}",name="remove_product_admin")
     * @return Response
     */
    public function removeProductAction(Request $request, $id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        if($product === null || !$product->getCategory()->isAvailable() || $product->isDeleted()){
            $this->addFlash('warning','Product Doesn\'t Exist!');
            return $this->redirectToRoute('view_products_panel',['id' => $product->getCategoryId()]);
        }

        $product->setDateDeleted(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        $this->addFlash('notice','Product Successfully Removed!');
        return $this->redirectToRoute('view_products_panel',['id' => $product->getCategoryId()]);
    }

    /**
     * @param $id
     * @param Request $request
     * @Route("/add/category/{id}", name="add_product_admin")
     * @return Response
     */
    public function addProductAction($id, Request $request)
    {
        $categoryIds = [];
        foreach ($this->getDoctrine()->getRepository(Category::class)->findAll() as $category)
            $categoryIds[] = $category->getId();

        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        if($category === null || !$category->isAvailable()){
            $this->addFlash('error','This Category Doesn\'t Exist!');
            return $this->redirectToRoute('view_products_panel',['id' => $id]);
        }

        $product = new Product();
        $product->setCategoryId($category->getId());
        $product->setCategory($category);

        $form = $this->createForm(ProductType::class,$product)
            ->add('submit', SubmitType::class, ['label' => 'Add','attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if(!in_array($form->getData()->getCategoryId(),$categoryIds)){
                $this->addFlash('warning','Invalid Category Id!');
                return $this->render('administration/products/productForm.html.twig',['form' => $form->createView()]);
            }
            if($product->isDiscountAdded() == false)$product->setDiscount(0)->setDateDiscountExpires(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('notice','Product Successfully Created!');
            return $this->redirectToRoute('view_products_panel',['id' => $id]);
        }
        return $this->render('administration/products/productForm.html.twig',['form' => $form->createView()]);
    }

    /**
     * @param $id
     * @param Request $request
     * @Route("/edit/{id}", name="edit_product_admin")
     * @return Response
     */
    public function editProductAction($id, Request $request)
    {
        $categoryIds = [];
        foreach ($this->getDoctrine()->getRepository(Category::class)->findAll() as $category)
            $categoryIds[] = $category->getId();

        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        if($product === null || $product->getDateDeleted() !== null){
            $this->addFlash('error','Product Doesn\' Exist!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        $form = $this->createForm(ProductType::class, $product)
            ->add('submit', SubmitType::class,['label' => 'Edit','attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if(!in_array($form->getData()->getCategoryId(),$categoryIds)){
                $this->addFlash('warning','Invalid Category Id!');
                return $this->render('administration/products/productForm.html.twig',['form' => $form->createView()]);
            }
            $product->setCategory($this->getDoctrine()->getRepository(Category::class)->find($product->getCategoryId()));
            if($product->isDiscountAdded() == false)$product->setDiscount(0)->setDateDiscountExpires(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('notice','Product Successfully Edited!');
            return $this->redirectToRoute('view_products_panel',['id' => $product->getCategoryId()]);
        }
        return $this->render('administration/products/productForm.html.twig',['form' => $form->createView()]);
    }

    /**
     * @param $request Request
     * @Route("/discount/all", name="discount_all_products" )
     * @return Response
     */
    public function discountAllProductsAction(Request $request)
    {
        $form = $this->createForm(DiscountType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $products = $this->getDoctrine()->getRepository(Product::class)->findAllAvailable();
            if(empty($products)){
                $this->addFlash('warning','NO Products Have Been Found!');
                return $this->redirectToRoute('view_products_in_categories_panel');
            }
            foreach ($products as $product){
                if($product->getDiscount() < $data['discount']){
                    $product->setDiscount($data['discount']);
                    $product->setDateDiscountExpires($data['dateDiscountExpires']);
                    $product->setDiscountAdded(true);

                    $em->flush();
                }
            }
            $this->addFlash('notice','All Products are at Discount!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        return $this->render('administration/products/discountAllForm.html.twig',['form' => $form->createView()]);

    }

    /**
     * @param int $id
     * @param Request $request
     * @Route("/product/{id}/comments", name="view_product_comments_admin")
     * @return Response
     */
    public function viewProductCommentsAction(int $id, Request $request)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        if($product === null || $product->isDeleted()){
            $this->addFlash('error','This Product Doesn\'t Exist!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        $comments = $this->get('knp_paginator')->paginate(
            $product->getComments(),
            $request->query->getInt('page',1),
            self::PAGE_COUNT
        );

        if(empty($comments->getItems())){
            $this->addFlash('warning','This Product Doesn\'t Have Any Comments!');
            return $this->redirectToRoute('view_products_panel',['id' => $product->getCategoryId()]);
        }

        return $this->render('administration/products/comments.html.twig',['comments' => $comments]);
    }

    /**
     * @param int $productId
     * @param int $commentId
     * @Route("/product/{productId}/comment/remove/{commentId}", name="remove_product_comment_admin")
     * @return Response
     */
    public function removeProductCommentAction(int $productId, int $commentId)
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->find($commentId);
        if($comment === null || $comment->isDeleted() || $comment->getProductId() !== $productId){
            $this->addFlash('warning','This Comment Doesn\'t Exist!');
            return $this->redirectToRoute('view_product_comments_admin',['id' => $productId]);
        }

        $comment->setDateDeleted(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('notice','Comment Successfully Removed!');
        return $this->redirectToRoute('view_product_comments_admin',['id' => $productId]);
    }

    /**
     * @param int $productId
     * @param int $commentId
     * @param Request $request
     * @Route("/product/{productId}/comment/edit/{commentId}", name="edit_product_comment_admin")
     * @return Response
     */
    public function editProductCommentAciont(int $productId, int $commentId, Request $request)
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->find($commentId);
        if($comment === null || $comment->isDeleted() || $comment->getProductId() !== $productId){
            $this->addFlash('warning','Comment Not Found!');
            return $this->redirectToRoute('view_product_comments_admin',['id' => $productId]);
        }

        $authorIds = [];
        $productIds = [];
        foreach ($this->getDoctrine()->getRepository(User::class)->findAll() as $user)
            $authorIds[] = $user->getId();
        foreach ($this->getDoctrine()->getRepository(Product::class)->findAll() as $product)
            $productIds[] = $product->getId();



        $form = $this->createForm(AdminCommentType::class,$comment)
            ->add('submit',SubmitType::class,['label'=>'Edit','attr'=>['class'=>'btn btn-primary']]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if(!in_array($form->getData()->getAuthorId(),$authorIds) || !in_array($form->getData()->getProductId(), $productIds)){
                $form->addError(new FormError('Invalid Author Id or Product Id!'));
                return $this->render('administration/products/commentForm.html.twig',['form'=>$form->createView()]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash('notice','Comment Edited Successfully!');
            return $this->redirectToRoute('view_product_comments_admin',['id' => $productId]);
        }
        return $this->render('administration/products/commentForm.html.twig',['form'=>$form->createView()]);
    }
}
