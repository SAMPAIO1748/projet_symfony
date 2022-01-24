<?php

namespace App\Controller\Admin;

use App\Entity\Licence;
use App\Form\LicenceType;
use App\Repository\LicenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminLicenceController extends AbstractController
{

    /**
     * @Route("admin/licences", name="admin_licence_list")
     */
    public function licenceList(LicenceRepository $licenceRepository)
    {
        $licences = $licenceRepository->findAll();

        return $this->render("admin/licences.html.twig", ['licences' => $licences]);
    }

    /**
     * @Route("admin/licence/{id}", name="admin_licence_show")
     */
    public function licenceShow(LicenceRepository $licenceRepository, $id)
    {
        $licence = $licenceRepository->find($id);

        return $this->render("admin/licence.html.twig", ['licence' => $licence]);
    }

    /**
     * @Route("admin/update/licence/{id}", name="licence_update")
     */
    public function licenceUpdate(
        LicenceRepository $licenceRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        $id
    ) {

        $licence = $licenceRepository->find($id);

        $licenceForm = $this->createForm(LicenceType::class, $licence);

        $licenceForm->handleRequest($request);

        if ($licenceForm->isSubmitted() && $licenceForm->isValid()) {

            $entityManagerInterface->persist($licence);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_licence_list");
        }

        return $this->render("admin/licenceform.html.twig", ['licenceForm' => $licenceForm->createView()]);
    }

    /**
     * @Route("admin/create/licence", name="licence_create")
     */
    public function licenceCreate(Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $licence = new Licence();

        $licenceForm = $this->createForm(LicenceType::class, $licence);

        $licenceForm->handleRequest($request);

        if ($licenceForm->isSubmitted() && $licenceForm->isValid()) {

            $entityManagerInterface->persist($licence);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_licence_list");
        }

        return $this->render("admin/licenceform.html.twig", ['licenceForm' => $licenceForm->createView()]);
    }

    /**
     * @Route("admin/delete/licence/{id}", name="licence_delete")
     */
    public function licenceDelete(
        $id,
        EntityManagerInterface $entityManagerInterface,
        LicenceRepository $licenceRepository
    ) {

        $licence = $licenceRepository->find($id);

        $entityManagerInterface->remove($licence);

        $entityManagerInterface->flush();

        return $this->redirectToRoute("admin_licence_list");
    }
}
