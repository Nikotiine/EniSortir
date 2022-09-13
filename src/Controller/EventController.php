<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event_lister', methods: 'GET')]
    public function lister(EventRepository $eventRepository): Response
    {
        $events=$eventRepository->findAll();
        dump($events);
//        $events = $eventRepository->findEventsDefault();
        return $this->render('event/lister.html.twig',
        ["events"=>$events]);
    }
}