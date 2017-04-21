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
            10
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

        if($category === null || $category->getDateDeleted() !== null){
            $this->addFlash('error','This Category Doesn\'t Exist!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        $products = $this->get('knp_paginator')->paginate(
            $category->getAllProducts(),
            $request->query->getInt('page',1),
            10
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

        if($product === null || $product->getCategory()->getDateDeleted() !== null || $product->getDateDeleted() !== null){
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
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        if($category === null || $category->getDateDeleted() !== null){
            $this->addFlash('error','This Category Doesn\'t Exist!');
            return $this->redirectToRoute('view_products_panel',['id' => $id]);
        }

        $product = new Product();
        $product->setCategoryId($category->getId());
        $product->setCategory($category);

        $form = $this->createForm(ProductType::class,$product)
            ->add('submit', SubmitType::class, ['label' => 'Add','attr' => ['class' => 'btn btn-primary']]);
        if($request->isMethod('POST')){
            $this->validateForm($request,$form);
            if($form->isSubmitted() && $form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();

                $this->addFlash('notice','Product Successfully Created!');
                return $this->redirectToRoute('view_products_panel',['id' => $id]);
            }
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
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if($product === null || $product->getDateDeleted() !== null){
            $this->addFlash('error','Product Doesn\' Exist!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        $form = $this->createForm(ProductType::class, $product)
            ->add('submit', SubmitType::class,['label' => 'Edit','attr' => ['class' => 'btn btn-primary']]);

        if($request->isMethod('POST')){
            $this->validateForm($request, $form);
            if($form->isSubmitted() && $form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();

                $this->addFlash('notice','Product Successfully Edited!');
                return $this->redirectToRoute('view_products_panel',['id' => $product->getCategoryId()]);

            }
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

    private function validateForm(Request $request, FormInterface $form)
    {
        $categoryIds = [];
        $categs = $this->getDoctrine()->getRepository(Category::class)->findAll();
        foreach ($categs as $category) {
            $categoryIds[] = $category->getId();
        }
        $requestParams = $request->request->all()['product'];
        if (array_key_exists('discountAdded', $requestParams)
            && new \DateTime(implode('-', $requestParams['dateDiscountExpires'])) <= new \DateTime('now')
            || array_key_exists('discountAdded', $requestParams)
            && date_format(new \DateTime(implode('-', $requestParams['dateDiscountExpires'])),'Y') < 2017
            || array_key_exists('discountAdded', $requestParams)
            && date_format(new \DateTime(implode('-', $requestParams['dateDiscountExpires'])),'Y') > 2020)
        {
            $form->addError(new FormError('Invalid Date! Date Range(2017 - 2020)'));
        }
        elseif($requestParams['name'] === ''
            || $requestParams['price'] === ''
            || $requestParams['description'] === ''
            || $requestParams['discount'] === ''
            || $requestParams['quantity'] === ''
            || $requestParams['categoryId'] === '')
        {
            $form->addError(new FormError('Form Data Cannot be Empty!'));
        }
        elseif(!is_numeric($requestParams['quantity'])
            || !is_numeric($requestParams['price'])
            || !is_numeric($requestParams['discount']))
        {
            $form->addError(new FormError('Quantity, Price and Discount Should be Valid Numbers!'));
        }
        elseif($requestParams['price'] <= 0)
        {
            $form->addError(new FormError('Price Cannot be Negative or Zero!'));
        }
        elseif($requestParams['discount'] < 0 || $requestParams['discount'] >= 99)
        {
            $form->addError(new FormError('Discount Should be Between 0% and 99%!'));
        }
        elseif($requestParams['quantity'] < 0)
        {
            $form->addError(new FormError('Product Quantity Cannot be Negative!'));
        }
        elseif(!in_array($requestParams['categoryId'], $categoryIds))
        {
            $form->addError(new FormError('Invalid Category Id!'));
        }
        else
        {
            $form->submit($request->request->get($form->getName()));
        }
    }

}
