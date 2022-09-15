<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventsListType;
use App\Form\EventType;
use App\Repository\CityRepository;
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
use function PHPUnit\Framework\throwException;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event_list', methods: ['GET', 'POST'])]
    public function list(
        EventRepository $eventRepository,
        UserRepository $userRepository,
        Request $request,
        EventService $service,
    ): Response
    {
        $fakeUser=$userRepository->find(3);//TODO remplacer $fakeUser par getUser()
        $events=[];

        $eventForm = $this->createForm(EventsListType::class);
        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted()){
            $choices = $eventForm->getData();
            $events = $service->loadEvents($choices, $fakeUser);
        }else{
            $events = $service->loadInitialEvents($fakeUser);
        }
        $service->formatList($events, $fakeUser);//formattage de la liste :
        //retrait des status "créé" si user != organizer et rangement par ordre croissant

        $allowedActions=$service->listAllowedActions($events,$fakeUser);

        for ($i=0 ; $i<count($events);$i++){
            if(($events[$i]->getStatus()->getId()==1) && ($events[$i]->getOrganizer() !== $fakeUser) ){
                unset($events[$i]);
            }
        }

        return $this->render('event/lister.html.twig', [
            "events"            =>  $events,
            "EventForm"         =>  $eventForm->createView(),
            "fakeUser"          =>  $fakeUser,
            "allowedActions"    =>  $allowedActions,
        ]);
    }
//            $eventRepository->getEventsPassed($events); TODO A creer pour le 4eme checkbox ("sortie passées")

    private function listEventsAsOrganizer(array &$events, EventRepository $eventRepository, ?User $fakeUser)
    {
        $eventsAsOrganizer= $eventRepository->getEventsAsOrganizer($fakeUser);
        foreach ($eventsAsOrganizer as $event){
            array_push($events, $event);
        }
    }

    private function listEventsAsRegistred(array &$events, EventRepository $eventRepository, ?User $fakeUser)
    {
        $eventsAsRegistred = $eventRepository->getEventsWhereRegistred($fakeUser);
        foreach ($eventsAsRegistred as $event){
            if(!in_array($event,$events,true))
                array_push($events, $event);
        }
    }

    private function listEventsWhereNotRegistred(array &$events, EventRepository $eventRepository, ?User $fakeUser)
    {
        $eventsWhereNotRegistred=$eventRepository->getEventsWhereNotRegistred($fakeUser);
        foreach ($eventsWhereNotRegistred as $event){
            if(!in_array($event,$events,true))
                array_push($events, $event);
        }
    }


    #[Route('/event/new/{id}', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function create(Request $request,User $user,
                           StatusRepository $statusRepository,CityRepository $cityRepository,
                           LocationRepository $locationRepository):Response
    {
        $idCity = $request->query->getInt('city');
        $event = new Event();
        $citys = $cityRepository->findAll();
        $campus = $user->getCampus();
        $loc = $locationRepository->findBy([
            'id'=>$idCity
        ]);
        $event->setCampus($campus);
        $event->setOrganizer($user);
        $event->setStatus($statusRepository->findOneBy([
            'wording'=>'Creer'
        ]));
        $form = $this->createForm(EventType::class,$event);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $idLocation = $request->request->getInt('location');
            foreach ($loc as $l){
                if($l->getId() == $idLocation){
                    $location = $l;
                }
            }
            $event = $form->getData();
            $event->setLocation($location);
            dump($event);
        }

       return $this->render('event/new_event.html.twig',[
            'form'=>$form->createView(),
            'citys'=>$citys,
            'campus'=>$campus,
            'locations'=>$loc,
            'idCity'=>$idCity
        ]);

    }

    #[Route('/event/details/{id}', name: 'app_event_details', methods: ['GET'])]
    public function detailEvent(Event $event) : Response {

        return $this->render(view: 'event/details_event.html.twig',parameters:[
            'event'=>$event,
            ]);
    }

}