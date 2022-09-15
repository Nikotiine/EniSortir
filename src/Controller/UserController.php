<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserModificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function edit(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(UserModificationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            dump($user);
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
}
