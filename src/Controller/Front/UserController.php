<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    /**
     * @Route("user/insert", name="user_insert")
     */
    public function userInsert(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        MailerInterface $mailerInterface
    ) {

        $user = new User();

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setRoles(["ROLE_USER"]);
            $user->setDate(new \DateTime("NOW"));

            $plainPassword = $userForm->get('password')->getData();
            $hashedPassword = $userPasswordHasherInterface->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $user_mail = $userForm->get('email')->getData();
            $user_name = $userForm->get('name')->getData();
            $user_firstname = $userForm->get('firstname')->getData();

            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            $email = (new TemplatedEmail())
                ->from('test@test.com')
                ->to($user_mail)
                ->subject('Inscription')
                ->htmlTemplate('front/email.html.twig')
                ->context([
                    'name' => $user_name,
                    'firstname' => $user_firstname
                ]);

            $mailerInterface->send($email);

            return $this->redirectToRoute('front_home');
        }

        return $this->render("front/userform.html.twig", ['userForm' => $userForm->createView()]);
    }
}