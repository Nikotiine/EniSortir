<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{

    #[Route('/location/new/{origin}', name: 'app_location_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager,$origin): RedirectResponse
    {
        $location = new Location();
        $idEditEvent = $request->request->getInt('idOrigin');
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $location = $form->getData();
            $entityManager->persist($location);
            $entityManager->flush();
            $this->addFlash(
                'success', 'Lieu a été créé avec succès!'
            );
            if($idEditEvent !==0){
                return $this->redirectToRoute($origin,['id'=>$idEditEvent]);
            }
           return $this->redirectToRoute($origin);

        }
        if($idEditEvent !==0){
            return $this->redirectToRoute($origin,['id'=>$idEditEvent]);
        }
        return $this->redirectToRoute($origin);

    }

    #[Route('/location/mapping/{id}', name:'app_location_mapping', methods : ['GET', 'POST'])]
    public function mapping(Location $location, Event $event) : Response
    {

        return $this->render('location/mapping_location.html.twig', [
            'location'=>$location,
            'event'=>$event,
        ]);
    }
}
