<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventsListType;
use App\Form\EventType;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
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
    ): Response
    {
        $fakeUser=$userRepository->find(2);//TODO remplacer $fakeUser par getUser()
        $events=[];

        $event=new Event();
        $form = $this->createForm(EventsListType::class,$event);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            dump($request->request->get('checkBoxOrganizer'));
//            $choix = $request->query->get('checkBoxOrganizer');
//            dump($choix);
            //TODO : récupérer les params dans un tableau associatif et le passer en paramètre d'une methode du repo
        }else{
            $this->listEventsAsOrganizer($events, $eventRepository, $fakeUser);
            $this->listEventsAsRegistred($events, $eventRepository, $fakeUser);
            $this->listEventsWhereNotRegistred($events, $eventRepository, $fakeUser);
        }

        for ($i=0 ; $i<count($events);$i++){
            if(($events[$i]->getStatus()->getId()==1) && ($events[$i]->getOrganizer() !== $fakeUser) ){
                unset($events[$i]);
            }
        }

        return $this->render('event/lister.html.twig', [
            "events"        =>  $events,
            "EventForm"     =>  $form->createView(),
            "fakeUser"      =>  $fakeUser
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
    public function create(Request $request,User $user,StatusRepository $statusRepository,CityRepository $cityRepository):Response{
        $event = new Event();
        $citys = $cityRepository->findAll();
        $campus = $user->getCampus();
       // dd($user);
        $event->setCampus($campus);
        $event->setOrganizer($user);
        $event->setStatus($statusRepository->findOneBy([
            'wording'=>'Creer'
        ]));


        $form = $this->createForm(EventType::class,$event);

       return $this->render('event/new_event.html.twig',[
            'form'=>$form->createView(),
           'citys'=>$citys,
           'campus'=>$campus
        ]);

    }
}