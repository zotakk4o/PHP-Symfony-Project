<?php

namespace VehturiinikShopBundle\Controller\Administration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use VehturiinikShopBundle\Entity\Category;
use VehturiinikShopBundle\Form\CategoryType;

/**
 * Class CategoryController
 * @package VehturiinikShopBundle\Controller
 * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_EDITOR')")
 * @Route("/administration")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/categories", name="view_category_panel")
     * @return Response
     */
    public function viewCategoriesAction()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAllAvailable();

        if(empty($categories)){
            $this->addFlash('warning','No Categories Found');
            return $this->redirectToRoute('add_category');
        }

        return $this->render('administration/categories/categories.html.twig',['categories' => $categories]);
    }

    /**
     * @param int $id
     * @Route("/categories/remove/{id}", name="remove_category")
     * @return Response
     */
    public function removeCategoryAction($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        if($category === null || $category->getDateDeleted() !== null){
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
     * @Route("/categories/edit/{id}", name="edit_category")
     * @return Response
     */
    public function editCategoryAction($id, Request $request)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        if($category === null || $category->getDateDeleted() !== null){
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
     * @Route("/categories/add",name="add_category")
     * @return Response
     */
    public function addCategoryAction(Request $request)
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category)
            ->add('submit',SubmitType::class,['label' => 'Add', 'attr' => ['class' => 'btn btn-primary']]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();

            $em->persist($category);
            $em->flush();

            $this->addFlash('notice','Category Successfully Created!');
            return $this->redirectToRoute('view_category_panel');
        }

        return $this->render('administration/categories/addAndDelete.html.twig',['form' => $form->createView()]);
    }
}
