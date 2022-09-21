<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Status;
use App\Entity\User;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use App\Repository\StatusRepository;


class EventService
{
    public function __construct(private StatusRepository $statusRepository,
                                private LocationRepository $locationRepository,)
    {
    }

    /**
     * Initialise la date des input et le campus dans le formualire
     * Attibut le user comme organisateur
     * @param Event $event
     * @param User $user
     * @return Event
     */
    public function initFormNewEvent(Event $event ,User $user):Event
    {

        $event->setCampus($user->getCampus());
        $event->setOrganizer($user);
        $event->setStartAt(new \DateTimeImmutable());
        $event->setDeadLineInscriptionAt(new \DateTimeImmutable());
        $event->setStatus($this->statusRepository->findOneBy([
            'wording'=>Status::CREATE
        ]));
        return $event;
    }

    /**
     * Set le campus de la sortie , set le lieu
     * @param Event $event
     * @param int $idLocation
     * @param User $user
     * @return Event
     */
    public function formIsValid(Event $event,int $idLocation,User $user):Event
    {
        $event->setCampus($user->getCampus());
        $event->setLocation($this->locationRepository->findOneBy([
            'id'=>$idLocation
        ]));
        return $event;
    }



}
