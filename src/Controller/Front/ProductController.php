<?php

namespace App\Controller\Front;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    /**
     * @Route("products", name="product_list")
     */
    public function productList(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();

        return $this->render("front/products.html.twig", ['products' => $products]);
    }

    /**
     * @Route("product/{id}", name="product_show")
     */
    public function productShow(
        ProductRepository $productRepository,
        $id,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository
    ) {
        $product = $productRepository->find($id);

        $comment = new Comment();

        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $user = $this->getUser();
            if ($user) {
                $user_mail = $user->getUserIdentifier();
                $user = $userRepository->findOneBy(['email' => $user_mail]);

                $comment->setUser($user);
                $comment->setProduct($product);
                $comment->setDate(new \DateTime("NOW"));

                $entityManagerInterface->persist($comment);
                $entityManagerInterface->flush();
            }
        }



        return $this->render("front/product.html.twig", [
            'product' => $product,
            'commentForm' => $commentForm->createView()
        ]);
    }
}
