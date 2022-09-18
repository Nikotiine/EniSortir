<?php

namespace App\Command;

use App\Entity\Status;
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

    public function verifyCheckRegistration(): void
    {

        $params = [ Status::OPEN , Status::CLOSE];
        $events = $this->eventRepository->getActiveEvents($params);
       // dump($events);
        foreach ($events as $event) {
            // Trouvez le nbe d'inscrit et le comparer au max
            $totalRegistration = $event->getRegistration();
            dump($totalRegistration);
            $maxRegistration = $event->getMaxPeople();
            dump($maxRegistration);
            // si max == nbe inscrit =< status cloturer
            // si max < nb inscritp status open
        }
    }
}