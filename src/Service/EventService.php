<?php

namespace App\Service;

use App\Entity\Campus;
use App\Entity\User;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Event\Event;

class EventService
{
    public function __construct(private UserRepository $userRepository,
                                private CampusRepository $campusRepository,
                                private EventRepository $repository)
    {
    }

    /************ Chris*****************/

    private function listEventsWithParams(
        string $eventType,
        array &$events,
        array $filters,
        UserInterface $connectedUser
    ) {
        $eventsQueried = $this->repository->getEventsWithParams($eventType, $filters, $connectedUser);
        foreach ($eventsQueried as $event) {
            if (!in_array($event, $events, true)) {
                array_push($events, $event);
            }
        }
    }

    public function loadEvents(mixed $choices, UserInterface $connectedUser)
    {
        $events = [];
        if ($choices['campus']) {
            $filters['campus'] = $choices['campus'];
        }
        if ($choices['searchBar']) {
            $filters['searchBar'] = $choices['searchBar'];
        }
        if ($choices['minDate']) {
            $filters['minDate'] = $choices['minDate'];
        }
        if ($choices['maxDate']) {
            $filters['maxDate'] = $choices['maxDate'];
        }

        if ($choices['checkBoxOrganizer']) {
            $this->listEventsWithParams('AsOrganizer', $events, $filters, $connectedUser);
        }
        if ($choices['checkBoxRegistred']) {
            $this->listEventsWithParams('WhereRegistred', $events, $filters, $connectedUser);
        }
        if ($choices['checkBoxNotRegistred']) {
            $this->listEventsWithParams('WhereNotRegistred', $events, $filters, $connectedUser);
        }
        if ($choices['checkBoxEventsPassed']) {
            $this->listEventsWithParams('PassedEvents', $events, $filters, $connectedUser);
        }

        return $events;
    }

    public function loadInitialEvents(UserInterface $connectedUser)
    {
        $filters = [];
        $events = [];
        $this->listEventsWithParams('AsOrganizer', $events, $filters, $connectedUser);
        $this->listEventsWithParams('WhereRegistred', $events, $filters, $connectedUser);
        $this->listEventsWithParams('WhereNotRegistred', $events, $filters, $connectedUser);

        return $events;
    }

    /**
     * @param array Tableau contenant les Events correspondants aux filtres de recherche
     *
     * @return void Supprime les Events "en création" dont l'utilisateur connecté n'est pas "l'Organizer"
     */
    public function formatList(array &$events, UserInterface $connectedUser)
    {
        for ($i = 0; $i < count($events); ++$i) {
            if ((1 == $events[$i]->getStatus()->getId()) && ($events[$i]->getOrganizer() !== $connectedUser)) {
                unset($events[$i]);
            }
        }

        // TODO : reorganiser les sorties par ordre croissant (avec usort()?)
//        function comparator( $event1,  $event2 ){
//            return ($event1->getStartAt() < $event2->getStartAt());
//        }
//        usort($events,"comparator");
    }

    /**
     * @param array Tableau contenant les Events qui seront affichées
     *
     * @return array Tableau associatif avec Key = Event.id et Value = actions autorisées
     */
    public function listAllowedActions(array $events, UserInterface $connectedUser)
    {
        $allowedActions = [];
        foreach ($events as $event) {
            $action1 = '';
            $action2 = '';
            if (1 == $event->getStatus()->getId()) {// si statut "Créer" (seules les sortie "créées"
                // de l'utilisateur connecté sont dans $events, dc pas besoin de vérifier organizer=getUser)
                $action1 .= "<a href='/event/modifier'>Modifier</a>";
                $action2 .= " - <a href='/event/publier'>Publier</a> ";
            } else {// affichage de toutes les autres
                $action1 .= "<a href='/event/afficher'>Afficher</a> ";
            }
            if (2 == $event->getStatus()->getId() || 3 == $event->getStatus()->getId()) {// si statut "Ouvert ou Cloturée"
                if (2 == $event->getStatus()->getId()
                    && $event->getOrganizer() == $connectedUser) {// si ouvert et l'utilisateur connecté et l'organisateur
                    $action2 .= " - <a href='/event/annuler'>Annuler</a>";
                }
                if ($event->getRegistration()->contains($connectedUser)) {// si inscrit sur la sortie
                    $action2 .= " - <a href='/event/desister'>Se désister</a>";
                } elseif (2 == $event->getStatus()->getId()
                    && ($event->getMaxPeople() > count($event->getRegistration()))) {// si ouvert et places disponibles
                    $action2 .= " - <a href='/event/inscrire'>S'inscrire</a>";
                }
            }
            $allowedActions[$event->getId()] = $action1.$action2;
        }

        return $allowedActions;
    }

    /************ FinChris*****************/

    public function getCampus(): array
    {
        return $this->campusRepository->findAll();
    }

    public function filteredEvents(User $user, int $campus, string $key, string $startAt, string $finishAt,
                                   bool $organizer, bool $registered, bool $notRegistered,
                                    bool $oldEvents): array
    {
        $selectedCampus = $this->selectedCampus($campus);
        $filteredList = [];
        //  $start = new \DateTimeImmutable($startAt);
        //  $finish =  new \DateTimeImmutable($finishAt);
        if ($oldEvents) {
            return $this->repository->oldEvents();
        }
        $filtered = $this->repository->filterEvents($selectedCampus);
        foreach ($filtered as $event) {
            if ($organizer && $event->getOrganizer()->getId() == $user->getId()) {
                $filteredList[] = $event;
            }
        }
        if ($registered) {
            $regiteredEvents = $this->repository->filterEventsRegistered($selectedCampus, $user);
            dump($regiteredEvents);
            foreach ($regiteredEvents as $egiteredEvent) {
                $filteredList[] = $egiteredEvent;
            }
        }
        if ($notRegistered) {
            $notRegiteredEvents = $this->repository->filterEventsNotRegistered($selectedCampus, $user);
            foreach ($notRegiteredEvents as $notRegiteredEvent) {
                $filteredList[] = $notRegiteredEvent;
            }
        }

        if (strlen($key) > 0) {
            $withKeysearch = [];

            foreach ($filteredList as $event) {
                if (str_contains(strtolower($event->getName()), strtolower($key))) {
                    $withKeysearch[] = $event;
                }
            }

            return $withKeysearch;
        }

        return $filteredList;
    }

    private function selectedCampus(int $id): Campus
    {
        return $this->campusRepository->findOneBy([
            'id' => $id,
        ]);
    }
}
