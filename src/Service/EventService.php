<?php

namespace App\Service;

use App\Entity\Campus;
use App\Entity\User;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Vich\UploaderBundle\Event\Event;

class EventService
{

    public function __construct(private UserRepository $userRepository,
                                private CampusRepository $campusRepository,
                                private EventRepository $repository)
    {
    }

    /*************************************/
    /************ Chris*****************/
    /*************************************/
    public function listEventsWithParams(
        string $eventType,
        array &$events,
        array $filters,
        User $fakeUser
    )
    {
        $eventsQueried=$this->repository->getEventsWithParams($eventType, $filters, $fakeUser);
        foreach ($eventsQueried as $event){
            if(!in_array($event,$events,true))
                array_push($events, $event);
        }
    }
    public function loadEvents(mixed $choices, User $fakeUser)
    {
        $events=[];
        if($choices['campus']){
            $filters['campus']=$choices['campus'];
        }
        if($choices['searchBar']){
            $filters['searchBar']=$choices['searchBar'];
        }
        if($choices['minDate']){
            $filters['minDate']=$choices['minDate'];
        }
        if($choices['maxDate']){
            $filters['maxDate']=$choices['maxDate'];
        }

        if($choices['checkBoxOrganizer']){
            $this->listEventsWithParams("AsOrganizer",$events, $filters, $fakeUser);
        }
        if($choices['checkBoxRegistred']){
            $this->listEventsWithParams("WhereRegistred",$events, $filters, $fakeUser);
        }
        if($choices['checkBoxNotRegistred']){
            $this->listEventsWithParams("WhereNotRegistred",$events, $filters, $fakeUser);
        }
        if($choices['checkBoxEventsPassed']){
            $this->listEventsWithParams("PassedEvents",$events, $filters, $fakeUser);
        }
        return $events;
    }
    public function loadInitialEvents(User $fakeUser)
    {
        $filters=[];
        $events=[];
        $this->listEventsWithParams("AsOrganizer",$events, $filters, $fakeUser);
        $this->listEventsWithParams("WhereRegistred",$events, $filters, $fakeUser);
        $this->listEventsWithParams("WhereNotRegistred",$events, $filters, $fakeUser);
        return $events;
    }
    public function formatList(array &$events, User $fakeUser)
    {
        for ($i=0 ; $i<count($events);$i++){
            if(($events[$i]->getStatus()->getId()==1) && ($events[$i]->getOrganizer() !== $fakeUser) ){
                unset($events[$i]);
            }
        }
//        function comparator(\App\Entity\Event $event1, \App\Entity\Event $event2 ){
//            return ($event1->getStartAt() > $event2->getStartAt());
//        }
//        usort($events,'comparator');
    }
    /*************************************/
    /************ FinChris*****************/
    /*************************************/

    public function getCampus():array
    {
        return $this->campusRepository->findAll();
    }
    public function filteredEvents(User $user,int $campus, string $key,string $startAt,string $finishAt,
                                   bool $organizer,bool $registered,bool $notRegistered,
                                    bool $oldEvents ):array
    {
        $selectedCampus = $this->selectedCampus($campus);
        $filteredList=[];
      //  $start = new \DateTimeImmutable($startAt);
      //  $finish =  new \DateTimeImmutable($finishAt);
        if($oldEvents){
             return  $this->repository->oldEvents();
        }
        $filtered= $this->repository->filterEvents($selectedCampus);
        foreach ($filtered as $event){
           if($organizer && $event->getOrganizer()->getId()==$user->getId()){
               $filteredList[] = $event;
           }
        }
        if($registered){
            $regiteredEvents = $this->repository->filterEventsRegistered($selectedCampus,$user);
            dump($regiteredEvents);
            foreach ($regiteredEvents as $egiteredEvent){
                $filteredList[] = $egiteredEvent;
            }

        }
        if($notRegistered){
            $notRegiteredEvents = $this->repository->filterEventsNotRegistered($selectedCampus,$user);
            foreach ($notRegiteredEvents as $notRegiteredEvent){
                $filteredList[] = $notRegiteredEvent;

            }
        }

        if(strlen($key)>0){
            $withKeysearch=[];

            foreach ($filteredList as $event){
                if(str_contains(strtolower($event->getName()), strtolower($key))){
                    $withKeysearch[]=$event;
                }
            }
            return $withKeysearch;
        }


        return $filteredList;


    }
    private function selectedCampus(int $id):Campus
    {
        return $this->campusRepository->findOneBy([
            'id'=>$id
        ]);
    }




}