<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    //solution 1
//    #[Route('/event', name: 'event_lister', methods: 'GET')]
//    public function lister(
//        EventRepository $eventRepository,
//        StatusRepository $statusRepository,
//        UserRepository $userRepository
//    ): Response
//    {
//        $statusPassee=$statusRepository->findOneBy(['wording'=>'Passée']);
//        $statusAnnulee=$statusRepository->findOneBy(['wording'=>'Annulée']);
//        $events = $eventRepository->getEventsWithParams($statusPassee->getId(),$statusAnnulee->getId());
//
//        $fakeUser=$userRepository->find(82);//TODO remplacer $fakeUser par getUser()
//        $statusCreer=$statusRepository->findOneBy(['wording'=>'Créer']);
//
//        for ($i=0 ; $i<count($events);$i++){
//            if(($events[$i]->getStatus()==$statusCreer) && ($events[$i]->getOrganizer() !== $fakeUser) ){
//                unset($events[$i]);
//            }
//        }
//
//        return $this->render('event/lister.html.twig', [
//            "events"=>$events,
//        ]);



        //if ("je suis l'organisateur" est coché) {$parameters["asOrganiser"] = true}
        //if ("je suis inscrit" est coché) {$parameters["registred"] = true}
        //if ("sorties auxquelles je ne suis pas inscrit" est coché){$parameters["notRegistred"]=true}

        //if ("sorties passées" est coché) {$parameters["passed"]=true}
        //$parameters["campus"]="choix du campus";
        //if ("recherche par nom de sortie" rensignée) {$parameters["seacrhByKeyWords"]="laSaisie"}
        //if ("date début renseignée") {$parameters["dateAfter"]="date choisie"}
        //if ("date fin renseignée") {$parameters["dateBefore"]="date choisie"}
        //appel de la méthode getEventsWithParams($parameters)

    #[Route('/event', name: 'event_lister', methods: 'GET')]
    public function lister(
        EventRepository $eventRepository,
        StatusRepository $statusRepository,
        UserRepository $userRepository
    ): Response
    {
        $fakeUser=$userRepository->find(90);//TODO remplacer $fakeUser par getUser()

        $parameters["asOrganizer"]=true;
        $parameters["registred"]=true;
        $parameters["notRegistred"]=true;
        $parameters=[];
        $events = $eventRepository->getEventsWithParams($parameters, $fakeUser);


        $statusCreer=$statusRepository->findOneBy(['wording'=>'Créer']);

        for ($i=0 ; $i<count($events);$i++){
            if(($events[$i]->getStatus()==$statusCreer) && ($events[$i]->getOrganizer() !== $fakeUser) ){
                unset($events[$i]);
            }
        }

        return $this->render('event/lister.html.twig', [
            "events"=>$events,
        ]);
    }
}