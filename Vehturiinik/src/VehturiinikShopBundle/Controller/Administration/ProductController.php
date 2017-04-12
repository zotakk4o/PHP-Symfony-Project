<?php

namespace VehturiinikShopBundle\Controller\Administration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Entity\Product;
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
     * @Route("/", name="view_products_in_categories_panel")
     * @return Response
     */
    public function viewProductsInCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAllAvailable();

        if(empty($categories)){
            $this->addFlash('warning','No Categories Found');
            return $this->redirectToRoute('add_category');
        }

        return $this->render('administration/products/categories.html.twig',['categories' => $categories]);
    }

    /**
     * @param $id
     * @Route("/category/{id}", name="view_products_panel")
     * @return Response
     */
    public function viewProductsInCategoryAction($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        if($category === null || $category->getDateDeleted() !== null){
            $this->addFlash('error','This Category Doesn\'t Exist!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        $products = $category->getAllProducts();

        if(empty($products)){
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
            ->add('categoryId', HiddenType::class)
            ->add('submit', SubmitType::class, ['label' => 'Add','attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
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
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if($product === null || $product->getDateDeleted() !== null){
            $this->addFlash('error','Product Doesn\' Exist!');
            return $this->redirectToRoute('view_products_in_categories_panel');
        }

        $form = $this->createForm(ProductType::class, $product)
            ->add('categoryId', HiddenType::class)
            ->add('submit', SubmitType::class,['label' => 'Edit','attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('notice','Product Successfully Edited!');
            return $this->redirectToRoute('view_products_panel',['id' => $product->getCategoryId()]);

        }

        return $this->render('administration/products/createAndEdit.html.twig',['form' => $form->createView()]);


    }

}
