<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class AutoStatusCommands
{

    public function __construct(private EventRepository        $eventRepository,
                                private StatusRepository       $statusRepository,
                                private EntityManagerInterface $manager)
    {
    }


    public function autoUpdatedStatus(): void
    {
        $params = [Status::CREATE, Status::IN_PROGRESS, Status::OPEN, Status::CLOSE];
        $now = new \DateTimeImmutable();
        $events = $this->eventRepository->getActiveEvents($params);
        foreach ($events as $event) {
            $startTime = $event->getStartAt();
            $finishTimeEvent = $startTime->modify("+" . $event->getDuration() . 'minutes');
            if ($startTime < $now && $finishTimeEvent > $now) {
                $event->setStatus($this->statusRepository->findOneBy([
                    'wording' => Status::IN_PROGRESS
                ]));
            }
            if ($finishTimeEvent < $now) {
                $event->setStatus($this->statusRepository->findOneBy([
                    'wording' => Status::PAST
                ]));
            }
            $this->manager->persist($event);
            $this->manager->flush();
        }


    }

    /**
     * Vérifie automatiquement le nombre d'inscriptions sur chaque événement en status ouvert et clôturé
     * @return void
     */
    public function verifyCheckRegistration(): void
    {

        $params = [Status::OPEN, Status::CLOSE];
        $events = $this->eventRepository->getOpenAndCloseEvents($params);
        foreach ($events as $event) {
            $maxRegistration = $event[Event::SELECTED_EVENT]->getMaxPeople();
            $totalRegistration = $event[Event::TOTAL_USER_REGISTERED];
            // Si le nombre d'inscrits est égale au nombre de places disponible, alors le status passe en CLOSE
            if ($totalRegistration == $maxRegistration) {
                $event[Event::SELECTED_EVENT]->setStatus($this->statusRepository->findOneBy([
                    'wording' => Status::CLOSE
                ]));
            }
            // Si le nombre d'inscrits est inférieur au nombre de places disponible, alors le status passe en OPEN
            if ($totalRegistration < $maxRegistration) {
                $event[Event::SELECTED_EVENT]->setStatus($this->statusRepository->findOneBy([
                    'wording' => Status::OPEN
                ]));
            }
            $this->manager->persist($event[Event::SELECTED_EVENT]);
            $this->manager->flush();
        }
    }

}