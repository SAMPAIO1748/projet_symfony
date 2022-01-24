<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminProductCntroller extends AbstractController
{

    /**
     * @Route("admin/products", name="admin_product_list")
     */
    public function productList(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();

        return $this->render("admin/products.html.twig", ['products' => $products]);
    }

    /**
     * @Route("admin/product/{id}", name="admin_product_show")
     */
    public function productShow(ProductRepository $productRepository, $id)
    {
        $product = $productRepository->find($id);

        return $this->render("admin/product.html.twig", ['product' => $product]);
    }

    /**
     * @Route("admin/update/product/{id}", name="product_update")
     */
    public function productUpdate(
        ProductRepository $productRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        $id
    ) {

        $product = $productRepository->find($id);

        $productForm = $this->createForm(ProductType::class, $product);

        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {

            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_product_list");
        }

        return $this->render("admin/productform.html.twig", ['productForm' => $productForm->createView()]);
    }

    /**
     * @Route("admin/create/product", name="product_create")
     */
    public function productCreate(Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $product = new Product();

        $productForm = $this->createForm(ProductType::class, $product);

        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {

            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_product_list");
        }

        return $this->render("admin/productform.html.twig", ['productForm' => $productForm->createView()]);
    }

    /**
     * @Route("admin/delete/product/{id}", name="product_delete")
     */
    public function productDelete(
        $id,
        EntityManagerInterface $entityManagerInterface,
        ProductRepository $productRepository
    ) {

        $product = $productRepository->find($id);

        $entityManagerInterface->remove($product);

        $entityManagerInterface->flush();

        return $this->redirectToRoute("admin_product_list");
    }
}
