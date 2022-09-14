<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use App\Form\EventsListType;
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
        StatusRepository $statusRepository,
        Request $request,
    ): Response
    {
        $fakeUser=$userRepository->find(5);//TODO remplacer $fakeUser par getUser()
        $statusAnnulee=$statusRepository->find(6);
        $events=[];
        $filters = [];

        $eventForm = $this->createForm(EventsListType::class);
        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted()){
            $choices = $eventForm->getData();

            if($choices['campus']){
                $filters['campus']=$choices['campus'];
            }
            if($choices['name']){
                $filters['name']=$choices['name'];
            }
            if($choices['minDate']){
                $filters['minDate']=$choices['minDate'];
            }
            if($choices['maxDate']){
                $filters['maxDate']=$choices['maxDate'];
            }

            if($choices['checkBoxOrganizer']){
                $this->listEventsWithParams("AsOrganizer",$events, $filters, $eventRepository, $fakeUser,$statusAnnulee);
            }
            if($choices['checkBoxRegistred']){
                $this->listEventsWithParams("WhereRegistred",$events, $filters, $eventRepository, $fakeUser,$statusAnnulee);
            }
            if($choices['checkBoxNotRegistred']){
                $this->listEventsWithParams("WhereNotRegistred",$events, $filters, $eventRepository, $fakeUser,$statusAnnulee);
            }
//            if($choices['checkBoxEventsPassed']){
//                $statusPassed=$statusRepository->find(5);
//                $this->listEventsWithParams("PassedEvents",$events, $filters, $eventRepository, $fakeUser,$statusAnnulee,$statusPassed);
//            }
    dump($choices);
            //TODO : récupérer un StatusRepository directement dans le EventRepository
        }else{
            $this->listEventsWithParams("AsOrganizer",$events, $filters, $eventRepository, $fakeUser,$statusAnnulee);
            $this->listEventsWithParams("WhereRegistred",$events, $filters, $eventRepository, $fakeUser,$statusAnnulee);
            $this->listEventsWithParams("WhereNotRegistred",$events, $filters, $eventRepository, $fakeUser,$statusAnnulee);
        }

        for ($i=0 ; $i<count($events);$i++){
            if(($events[$i]->getStatus()->getId()==1) && ($events[$i]->getOrganizer() !== $fakeUser) ){
                unset($events[$i]);
            }
        }
        dump($events);

        return $this->render('event/lister.html.twig', [
            "events"        =>  $events,
            "EventForm"     =>  $eventForm->createView(),
            "fakeUser"      =>  $fakeUser
        ]);
    }

    private function listEventsWithParams(
        string $eventType,
        array &$events,
        array $filters,
        EventRepository $eventRepository,
        User $fakeUser,
        Status $statusAnnulee,
    )
    {
            $eventsTmp=$eventRepository->getEventsWithParams($eventType, $filters, $fakeUser,$statusAnnulee);
        foreach ($eventsTmp as $event){
            if(!in_array($event,$events,true))
                array_push($events, $event);
        }
    }
}