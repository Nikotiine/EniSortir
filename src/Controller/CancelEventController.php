<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Form\CancelEventType;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CancelEventController extends AbstractController
{
    #[Route('/event/cancel/{id}', name: 'app_cancel_event', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === event.getOrganizer()")]
    public function cancelEvent(Event $event, EntityManagerInterface $manager, Request $request, StatusRepository $statusRepository): Response
    {
        // Recupere le status de la sortie a annulée
        $status = $event->getStatus()->getWording();

        // Si le status de la sortie est different de Creer ou Ouverte , redirection sur acceuil avec message d'erreur
        if (!str_contains(Status::CREATE, $status) && !str_contains(Status::OPEN, $status)) {
            $this->addFlash('failed', 'Annulation impossible');

            return $this->redirectToRoute('app_event_list');
        }
        $form = $this->createForm(CancelEventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $event->setStatus($statusRepository->findOneBy([
                'wording' => Status::CANCELED,
            ]));
            $manager->persist($event);
            $manager->flush();
            $this->addFlash('success', 'Sortie Annulee');

            return $this->redirectToRoute('app_event_list');
        }

        return $this->render('event/cancel/cancel_event.html.twig', [
         'form' => $form->createView(),
     ]);
    }
}
