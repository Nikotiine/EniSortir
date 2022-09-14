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