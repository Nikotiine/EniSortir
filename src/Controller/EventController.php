<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Form\EventsListType;
use App\Form\EventType;
use App\Model\EventsFilterModel;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event_list', methods: ['GET', 'POST'])]
    public function list(
        Request $request,
        EventRepository $eventRepository,
        UserRepository $userRepository,
    ): Response {
        $data = new EventsFilterModel();
        $user=$userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $data->campus = $user->getCampus();

        $form = $this->createForm(EventsListType::class, $data);
        $form->handleRequest($request);

        $events = $eventRepository->getEventList($data, $user);
        return $this->render('event/lister.html.twig', [
            'events' => $events,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/event/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function create(Request $request,
                           StatusRepository $statusRepository,CityRepository $cityRepository,
                           LocationRepository $locationRepository,EntityManagerInterface $manager):Response
    {
        $user = $this->getUser();
        $idLocation = $request->request->getInt('location');
        $event = new Event();
        $campus = $user->getCampus();
        dump($campus);
        $event->setOrganizer($user);
        $event->setStatus($statusRepository->findOneBy([
            'wording' => Status::CREATE,
        ]));
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $location = $locationRepository->findOneBy([
                'id' => $idLocation,
            ]);
            $event->setLocation($location);
            $event = $form->getData();
            $event->setCampus($campus);
            $manager->persist($event);
            $manager->flush();
        }

        return $this->render('event/new_event.html.twig', parameters: [
             'form' => $form->createView(),
             'edit' => false,
         ]);
    }

    #[Route('/event/edit/{id}', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Event $event, EntityManagerInterface $manager, Request $request): Response
    {
        dump($event->getLocation()->getCity()->getName());
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
            'idEvent'=>$form->getData()->getId()
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