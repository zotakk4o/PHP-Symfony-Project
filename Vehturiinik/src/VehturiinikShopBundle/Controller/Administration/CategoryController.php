<?php

namespace VehturiinikShopBundle\Controller\Administration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Entity\Product;
use VehturiinikShopBundle\Form\CategoryType;
use VehturiinikShopBundle\Form\DiscountType;

/**
 * Class CategoryController
 * @package VehturiinikShopBundle\Controller
 * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_EDITOR')")
 * @Route("/administration/categories")
 */
class CategoryController extends Controller
{
    const PAGE_COUNT = 10;
    /**
     * @param $request Request
     * @Route("/", name="view_category_panel")
     * @return Response
     */
    public function viewCategoriesAction(Request $request)
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

        return $this->render('administration/categories/categories.html.twig',['categories' => $categories]);
    }

    /**
     * @param int $id
     * @Route("/remove/{id}", name="remove_category")
     * @return Response
     */
    public function removeCategoryAction($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        if($category === null || !$category->isAvailable()){
            $this->addFlash('warning','Category Doesn\'t Exist!');
            return $this->redirectToRoute('view_category_panel');
        }

        $category->setDateDeleted(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();

        $this->addFlash('notice','Category Successfully Removed!');
        return $this->redirectToRoute('view_category_panel');
    }

    /**
     * @param $id
     * @param Request $request
     * @Route("/edit/{id}", name="edit_category")
     * @return Response
     */
    public function editCategoryAction($id, Request $request)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        if($category === null || !$category->isAvailable()){
            $this->addFlash('warning','Category Doesn\'t Exist!');
            return $this->redirectToRoute('view_category_panel');
        }

        $form = $this->createForm(CategoryType::class, $category)
            ->add('submit', SubmitType::class,['label' => 'Edit','attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();

                $this->addFlash('notice','Category Successfully Edited!');
                return $this->redirectToRoute('view_category_panel');
        }
        return $this->render('administration/categories/addAndDelete.html.twig',['form' => $form->createView()]);
    }

    /**
     * @param Request $request
     * @Route("/add",name="add_category")
     * @return Response
     */
    public function addCategoryAction(Request $request)
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category)
            ->add('submit',SubmitType::class,['label' => 'Add', 'attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($category);
            $em->flush();

            $this->addFlash('notice', 'Category Successfully Created!');
            return $this->redirectToRoute('view_category_panel');
        }
        return $this->render('administration/categories/addAndDelete.html.twig',['form' => $form->createView()]);
    }

    /**
     * @param $request Request
     * @param int $id
     * @Route("/{id}/discount/all", name="discount_category" )
     * @return Response
     */
    public function discountCategoryAction(Request $request, int $id)
    {
        $form = $this->createForm(DiscountType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $products = $this->getDoctrine()->getRepository(Product::class)->findBy(['categoryId' => $id]);
            if(empty($products)){
                $this->addFlash('warning','The Category Has NO Products!');
                return $this->redirectToRoute('view_category_panel');
            }
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
            return $this->redirectToRoute('view_category_panel');
        }
        return $this->render('administration/categories/discountForm.html.twig',['form' => $form->createView()]);
    }
}
