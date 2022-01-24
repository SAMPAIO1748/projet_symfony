<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminImageController extends AbstractController
{

    /**
     * @Route("admin/images", name="list_image")
     */
    public function listImage(ImageRepository $imageRepository)
    {
        $images = $imageRepository->findAll();

        return $this->render("admin/images.html.twig", ['images' => $images]);
    }

    /**
     * @Route("admin/image/{id}", name="show_image")
     */
    public function showImage(ImageRepository $imageRepository, $id)
    {
        $image = $imageRepository->find($id);

        return $this->render("admin/image.html.twig", ['image' => $image]);
    }

    /**
     * @Route("admin/create/image", name="create_image")
     */
    public function createImage(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        SluggerInterface $sluggerInterface
    ) {

        $image = new Image();

        $imageForm = $this->createForm(ImageType::class, $image);

        $imageForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {

            $imageFile = $imageForm->get('src')->getData();

            if ($imageFile) {

                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = $sluggerInterface->slug($originalFileName);

                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFileName
                );
            }

            $entityManagerInterface->persist($image);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_product_list");
        }

        return $this->render("admin/imageform.html.twig", ['imageForm' => $imageForm->createView()]);
    }

    public function updateImage(
        $id,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        SluggerInterface $sluggerInterface,
        ImageRepository $imageRepository
    ) {

        $image = $imageRepository->find($id);

        $imageForm = $this->createForm(ImageType::class, $image);

        $imageForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {

            $imageFile = $imageForm->get('src')->getData();

            if ($imageFile) {

                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = $sluggerInterface->slug($originalFileName);

                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFileName
                );
            }

            $entityManagerInterface->persist($image);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("list_image");
        }

        return $this->render("admin/imageform.html.twig", ['imageForm' => $imageForm->createView()]);
    }

    /**
     * @Route("admin/delete/image/{id}", name="delete_image")
     */
    public function deleteImage(
        $id,
        ImageRepository $imageRepository,
        EntityManagerInterface $entityManagerInterface
    ) {

        $image  = $imageRepository->find($id);

        $entityManagerInterface->remove($image);

        $entityManagerInterface->flush();

        return $this->redirectToRoute("list_image");
    }
}
