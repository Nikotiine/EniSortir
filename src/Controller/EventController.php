<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Form\EventsListType;
use App\Form\EventType;
use App\Model\EventsFilterModel;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\EventService;
use App\Service\StatusServices;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event_list', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function list(
        Request $request,
        EventRepository $eventRepository,
        UserRepository $userRepository,
        StatusServices $services
    ): Response {
        $services->verifyActiveStatus();
        $data = new EventsFilterModel();
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $data->campus = $user->getCampus();
        $form = $this->createForm(EventsListType::class, $data);
        $form->handleRequest($request);
        $data = $form->getData();
        dump($data);
        $events = $eventRepository->getEventList($data, $user);
        dump($events);

        return $this->render('event/lister.html.twig', [
            'events' => $events,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request,
                           UserRepository $userRepository,
                           EntityManagerInterface $manager,
                           EventService $service): Response
    {
        $user = $userRepository->findOneBy([
            'email'=>$this->getUser()->getUserIdentifier()
        ]);
        $idLocation = $request->request->getInt('location');
        $event = new Event();
        $event = $service->initFormNewEvent($event,$user);
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $event = $service->formIsValid($event,$idLocation,$user);
            $manager->persist($event);
            $manager->flush();
            $this->addFlash(
                'success', 'Votre sortie est enregistrer!'
            );
           return $this->redirectToRoute('app_event_edit',['id'=>$event->getId()]);

        }

        return $this->render('event/new_event.html.twig', [
             'form' => $form->createView(),
             'edit' => false,
            'activate'=>false
         ]);
    }

    #[Route('/event/edit/{id}', name: 'app_event_edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === event.getOrganizer()")]
    public function edit(Event $event, EntityManagerInterface $manager, Request $request): Response
    {
        //Permet d'afficher le bouton d'activation de la sortie
        $activate = false;
        if (str_contains(Status::CREATE,$event->getStatus()->getWording())){
            $activate = true;
        }

        $form = $this->createForm(EventType::class, $event,
            ['event_city' => $event->getLocation()->getCity()->getName()]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $manager->persist($event);
            $manager->flush();
        }

        return $this->render('event/new_event.html.twig', [
            'form' => $form->createView(),
            'edit' => true,
            'idEvent' => $form->getData()->getId(),
            'activate'=>$activate
        ]);
    }

    // TODO :: bloquer l'affichage des sorties antérieures à 1 mois ?(Christophe)
    #[Route('/event/details/{id}', name: 'app_event_details', methods: ['GET'])]
    public function detailEvent(Event $event): Response
    {
        return $this->render(view: 'event/details_event.html.twig', parameters: [
            'event' => $event,
            ]);
    }

    #[Route('/event/subscribe/{id}', name: 'app_event_subscribe', methods: ['GET'])]
    #[Security("event.getRegistration().count() < event.getMaxPeople()")]
    public function subscribeEvent(Event $event, EntityManagerInterface $manager , UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUserIdentifier()]);
        $event->addRegistration($user);
        $manager->persist($event);
        $manager->flush();
        $this->addFlash(
            'success', 'Votre inscription est confirmée!'
        );
        return $this->redirectToRoute('app_event_list');
    }

    #[Route('/event/unsubscribe/{id}', name: 'app_event_unsubscribe', methods: ['GET'])]
    public function unsubscribeEvent(Event $event, EntityManagerInterface $entityManager ,UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email'=>$this->getUser()->getUserIdentifier()]);
        $event->removeRegistration($user);
        $entityManager->persist($event);
        $entityManager->flush();
        $this->addFlash(
            'success', 'Votre inscription est annulée!'
        );

        return $this->redirectToRoute('app_event_list');
    }
}
