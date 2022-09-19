<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserModificationType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/edit/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === currentUser")]
    public function edit(User $currentUser, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(UserModificationType::class, $currentUser);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           // $currentUser->setUpdatedAt(new \DateTimeImmutable());
            $currentUser = $form->getData();
           // $currentUser->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($currentUser);
            $manager->flush();
            $this->addFlash(
                'success', 'Votre profil a été modifié avec succès!'
            );
           return $this->redirectToRoute('app_login');
        }

        return $this->render('user/edit.html.twig',
            ['form' => $form->createView()]);
    }

    #[Route('/user/edit-password/{id}', name: 'app_user_edit-password', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === currentUser")]
    public function editPassword(User $currentUser,
                                 Request $request,
                                 UserPasswordHasherInterface $hasher,
                                 EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($currentUser, $form->getData()['plainPassword'])) {
                $currentUser->setPassword(
                    $hasher->hashPassword($currentUser, $form->getData()['newPassword'])
                );

                $manager->persist($currentUser);
                $manager->flush();
                $this->addFlash(
                    'success', 'Votre mot de passe a été modifié avec succès!'
                );

                return $this->redirectToRoute('app_user_edit-password');
            } else {
                $this->addFlash(
                    'warning', 'Le mot de passe est incorrect');
            }
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/profil/{id}', name:'app_user_profil',methods:['GET'])]
    public function showProfilUser (User $user) : Response {
        return $this->render('user/profil.html.twig', parameters: [

        ]);
    }
}
