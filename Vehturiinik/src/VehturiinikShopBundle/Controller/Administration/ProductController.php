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
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Entity\User;
use VehturiinikShopBundle\Form\DiscountType;
use VehturiinikShopBundle\Form\ProductType;

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
            $category->getAllProducts(),
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
        if($product === null || !$product->getCategory()->isAvailable() || !$product->isAvailable()){
            $this->addFlash('warning','Product Doesn\'t Exist!');
            return $this->redirectToRoute('view_products_panel');
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
                return $this->render('administration/products/createAndEdit.html.twig',['form' => $form->createView()]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('notice','Product Successfully Created!');
            return $this->redirectToRoute('view_products_panel',['id' => $id]);
        }
        return $this->render('administration/products/createAndEdit.html.twig',['form' => $form->createView()]);
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
                return $this->render('administration/products/createAndEdit.html.twig',['form' => $form->createView()]);
            }
            $product->setCategory($this->getDoctrine()->getRepository(Category::class)->find($product->getCategoryId()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('notice','Product Successfully Edited!');
            return $this->redirectToRoute('view_products_panel',['id' => $product->getCategoryId()]);
        }
        return $this->render('administration/products/createAndEdit.html.twig',['form' => $form->createView()]);
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
            $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
            foreach ($products as $product){
                if($product->getDiscount() < $data['discount']){
                    $product->setDiscount($data['discount']);
                    $product->setDateDiscountExpires($data['dateDiscountExpires']);
                    $product->setDiscountAdded(true);

                    $em->persist($product);
                    $em->flush();
                }
            }
            $this->addFlash('notice','All Products are at Discount!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        return $this->render('administration/products/discountAllForm.html.twig',['form' => $form->createView()]);

    }
}
