<?php

namespace App\Controller\Front;

use App\Entity\Like;
use App\Entity\Comment;
use App\Entity\Dislike;
use App\Form\CommentType;
use App\Repository\LikeRepository;
use App\Repository\UserRepository;
use App\Repository\DislikeRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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


    /**
     * @Route("like/product/{id}", name="like_product")
     */
    public function likeProduct(
        $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManagerInterface,
        LikeRepository $likeRepository,
        DislikeRepository $dislikeRepository
    ) {

        $product = $productRepository->find($id);
        $user = $this->getUser();


        if (!$user) {
            return $this->json([
                'code' => 403,
                'message' => 'Vous devez être connecté'
            ], 403);
        }


        if ($product->isLikeByUser($user)) {
            $like = $likeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);

            $entityManagerInterface->remove($like);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Like supprimé",
                'likes' => $likeRepository->count(['product' => $product])
            ], 200);
        }

        if ($product->isDislikeByUser($user)) {
            $dislike = $dislikeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);

            $entityManagerInterface->remove($dislike);

            $like = new Like();

            $like->setProduct($product);
            $like->setUser($user);

            $entityManagerInterface->persist($like);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Like ajouté et dislike supprimé",
                'likes' => $likeRepository->count(['product' => $product]),
                'dislikes' => $dislikeRepository->count(['product' => $product])
            ], 200);
        }

        $like = new Like();

        $like->setProduct($product);
        $like->setUser($user);

        $entityManagerInterface->persist($like);
        $entityManagerInterface->flush();


        return $this->json([
            'code' => 200,
            'message' => "Like ajouté",
            'likes' => $likeRepository->count(['product' => $product])
        ], 200);
    }

    /**
     * @Route("dislike/product/{id}", name="dislike_product")
     */
    public function dislikeProduct(
        $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManagerInterface,
        LikeRepository $likeRepository,
        DislikeRepository $dislikeRepository
    ) {

        $product = $productRepository->find($id);
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'code' => 403,
                'message' => 'Vous devez être connecté'
            ], 403);
        }

        if ($product->isDislikeByUser($user)) {
            $dislike = $dislikeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);

            $entityManagerInterface->remove($dislike);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Le dislike a été supprimé",
                'dislikes' => $dislikeRepository->count(['product' => $product])
            ], 200);
        }

        if ($product->isLikeByUser($user)) {
            $like = $likeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);

            $entityManagerInterface->remove($like);

            $dislike = new Dislike();

            $dislike->setProduct($product);
            $dislike->setUser($user);

            $entityManagerInterface->persist($dislike);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Like supprimé et dislike ajouté",
                'likes' => $likeRepository->count(['product' => $product]),
                'dislikes' => $dislikeRepository->count(['product' => $product])
            ], 200);
        }

        $dislike = new Dislike();

        $dislike->setProduct($product);
        $dislike->setUser($user);

        $entityManagerInterface->persist($dislike);
        $entityManagerInterface->flush();

        return $this->json([
            'code' => 200,
            'message' => "Dislike ajouté",
            'dislikes' => $dislikeRepository->count(['product' => $product])
        ], 200);
    }
}
