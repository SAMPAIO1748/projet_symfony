<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminCategoryController extends AbstractController
{

    /**
     * @Route("admin/categories", name="admin_category_list")
     */
    public function categoryList(CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->findAll();

        return $this->render("admin/categories.html.twig", ['categories' => $categories]);
    }

    /**
     * @Route("admin/category/{id}", name="admin_category_show")
     */
    public function categoryShow(CategoryRepository $categoryRepository, $id)
    {
        $category = $categoryRepository->find($id);

        return $this->render("admin/category.html.twig", ['category' => $category]);
    }

    /**
     * @Route("admin/update/category/{id}", name="category_update")
     */
    public function categoryUpdate(
        CategoryRepository $categoryRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        $id
    ) {

        $category = $categoryRepository->find($id);

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {

            $entityManagerInterface->persist($category);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_category_list");
        }

        return $this->render("admin/categoryform.html.twig", ['categoryForm' => $categoryForm->createView()]);
    }

    /**
     * @Route("admin/create/category", name="category_create")
     */
    public function categoryCreate(Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $category = new Category();

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {

            $entityManagerInterface->persist($category);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_category_list");
        }

        return $this->render("admin/categoryform.html.twig", ['categoryForm' => $categoryForm->createView()]);
    }

    /**
     * @Route("admin/delete/category/{id}", name="category_delete")
     */
    public function categoryDelete(
        $id,
        EntityManagerInterface $entityManagerInterface,
        CategoryRepository $categoryRepository
    ) {

        $category = $categoryRepository->find($id);

        $entityManagerInterface->remove($category);

        $entityManagerInterface->flush();

        return $this->redirectToRoute("admin_category_list");
    }
}
