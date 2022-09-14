<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use App\Form\EventsListType;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\EventService;
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
        EventService $service,
    ): Response
    {

        $fakeUser2=$userRepository->find(6);
        $campus = $service->getCampus();
        $campusId = $request->query->getInt('campus');
        $key = $request->query->getAlnum('key');
        $startAt = $request->query->getAlnum('startAt');
        $finsihAt = $request->query->getAlnum('finishAt');
        $organizer = true;
        $registered = true;
        $notRegistered = true;
        $oldEvents = false;

        $events1 = $service->filteredEvents($fakeUser2, $fakeUser2->getCampus()->getId(),$key,$startAt,$finsihAt, true, true, true, false);
        if($request->query->get('search') !== null){
            $organizer = $request->query->getBoolean('orga');
            $registered = $request->query->getBoolean('registered');
            $notRegistered = $request->query->getBoolean('notRegistered');
            $oldEvents = $request->query->getBoolean('oldEvents');
            $events1 = $service->filteredEvents($fakeUser2,$campusId,$key,$startAt,$finsihAt,$organizer,$registered,$notRegistered,$oldEvents);
        }


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
        dump($organizer);

        return $this->render('event/lister.html.twig', [
            "events"=>$events1,
            "EventForm"=>$eventForm->createView(),
             "fakeUser"=>$fakeUser,
            'campus'=>$campus,
            'organizer'=>$organizer,
            'registered'=>$registered,
            'oldEvents'=>$oldEvents,
            'notRegistered'=>$notRegistered

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