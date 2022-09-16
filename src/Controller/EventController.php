<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventsListType;
use App\Form\EventType;
use App\Model\EventsFilterModel;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
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

    #[Route('/event/new/{id}', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function create(Request $request, User $user,
                           StatusRepository $statusRepository, CityRepository $cityRepository,
                           LocationRepository $locationRepository): Response
    {
        $idCity = $request->query->getInt('city');
        $event = new Event();
        $citys = $cityRepository->findAll();
        $campus = $user->getCampus();
        $loc = $locationRepository->findBy([
            'id' => $idCity,
        ]);
        $event->setCampus($campus);
        $event->setOrganizer($user);
        $event->setStatus($statusRepository->findOneBy([
            'wording' => 'Creer',
        ]));
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $idLocation = $request->request->getInt('location');
            foreach ($loc as $l) {
                if ($l->getId() == $idLocation) {
                    $location = $l;
                }
            }
            $event = $form->getData();
            $event->setLocation($location);
            dump($event);
        }

        return $this->render('event/new_event.html.twig', [
             'form' => $form->createView(),
             'citys' => $citys,
             'campus' => $campus,
             'locations' => $loc,
             'idCity' => $idCity,
         ]);
    }
}
