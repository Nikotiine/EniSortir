<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventsListType;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event_list', methods: ['GET', 'POST'])]
    public function list(
        EventRepository $eventRepository,
        UserRepository $userRepository,
        Request $request,
        EventService $service,
    ): Response {
        $fakeUser = $userRepository->find(3); // TODO remplacer $fakeUser par getUser()
        $events = [];

        $eventForm = $this->createForm(EventsListType::class);
        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted()) {
            $choices = $eventForm->getData();
            $events = $service->loadEvents($choices, $fakeUser);
        } else {
            $events = $service->loadInitialEvents($fakeUser);
        }
        $service->formatList($events, $fakeUser); // formattage de la liste :
        // retrait des status "créé" si user != organizer et rangement par ordre croissant

        $allowedActions = $service->listAllowedActions($events, $fakeUser);

        for ($i = 0; $i < count($events); ++$i) {
            if ((1 == $events[$i]->getStatus()->getId()) && ($events[$i]->getOrganizer() !== $fakeUser)) {
                unset($events[$i]);
            }
        }

        return $this->render('event/lister.html.twig', [
            'events' => $events,
            'EventForm' => $eventForm->createView(),
            'fakeUser' => $fakeUser,
            'allowedActions' => $allowedActions,
        ]);
    }
//            $eventRepository->getEventsPassed($events); TODO A creer pour le 4eme checkbox ("sortie passées")

    private function listEventsAsOrganizer(array &$events, EventRepository $eventRepository, ?User $fakeUser)
    {
        $eventsAsOrganizer = $eventRepository->getEventsAsOrganizer($fakeUser);
        foreach ($eventsAsOrganizer as $event) {
            array_push($events, $event);
        }
    }

    private function listEventsAsRegistred(array &$events, EventRepository $eventRepository, ?User $fakeUser)
    {
        $eventsAsRegistred = $eventRepository->getEventsWhereRegistred($fakeUser);
        foreach ($eventsAsRegistred as $event) {
            if (!in_array($event, $events, true)) {
                array_push($events, $event);
            }
        }
    }

    private function listEventsWhereNotRegistred(array &$events, EventRepository $eventRepository, ?User $fakeUser)
    {
        $eventsWhereNotRegistred = $eventRepository->getEventsWhereNotRegistred($fakeUser);
        foreach ($eventsWhereNotRegistred as $event) {
            if (!in_array($event, $events, true)) {
                array_push($events, $event);
            }
        }
    }

    #[Route('/event/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function create(Request $request,
                           StatusRepository $statusRepository,
                           LocationRepository $locationRepository,
        EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $idLocation = $request->request->getInt('location');
        $event = new Event();
        $campus = $user->getCampus();
        $event->setCampus($campus);
        $event->setOrganizer($user);
        $event->setStatus($statusRepository->findOneBy([
            'wording' => 'Creer',
        ]));
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $location = $locationRepository->findOneBy([
                'id' => $idLocation,
            ]);
            $event->setLocation($location);
            $event = $form->getData();
            $manager->persist($event);
            $manager->flush();
        }

        return $this->render('event/new_event.html.twig', [
             'form' => $form->createView(),
             'edit' => false,
         ]);
    }

    #[Route('/event/edit/{id}', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Event $event, EntityManagerInterface $manager, Request $request): Response
    {
        dump($event->getLocation()->getCity()->getName());
        $form = $this->createForm(EventType::class, $event, ['event_city' => $event->getLocation()->getCity()->getName()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $manager->persist($event);
            $manager->flush();
        }

        return $this->render('event/new_event.html.twig', [
            'form' => $form->createView(),
            'edit' => true,
            'idLieux' => $event->getLocation()->getId(),
        ]);
    }

    #[Route('/event/details/{id}', name: 'app_event_details', methods: ['GET'])]
    public function detailEvent(Event $event): Response
    {
        return $this->render(view: 'event/details_event.html.twig', parameters: [
            'event' => $event,
            ]);
    }
}
