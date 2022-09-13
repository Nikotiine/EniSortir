<?php

namespace App\Controller;

use App\Entity\User;

use App\Form\UserModificationType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormTypeInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/edit/{id}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager,): Response
    {
        $form = $this->createForm(UserModificationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success', 'Votre profil a été modifié avec succès!'
            );

            return $this->redirectToRoute('app_user');

        }
        return $this->render('user/edit.html.twig',
            [
                'form' => $form->createView(),
            ]);
    }
    #[Route('/user/edit-password/{id}','app_user_editpassword ', methods: ['GET','POST'])]
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $hasher,EntityManagerInterface $manager) : Response
    {
        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword']))
            {
                $user->setPassword(
                $hasher->hashPassword($user,  $form->getData()['newPassword'])
                );
                $manager->persist($user);
                $manager->flush();
                $this->addFlash(
                    'success', 'Votre mot de passe a été modifié avec succès!'
                );
                return $this->redirectToRoute('app_user');
            }else{
                $this->addFlash(
                    'warning', 'Le mot de passe est incorrect');
            }
        }
        return  $this->render('user/edit_password.html.twig',[
            'form'=> $form->createView()
        ]);
    }
}
