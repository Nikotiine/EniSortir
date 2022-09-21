<?php

namespace App\Controller\Admin;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\CampusRepository;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        UserRepository   $userRepository,
        EventRepository  $eventRepository,
        CampusRepository $campusRepository,
        CityRepository   $cityRepository,
    )
    {
        $this->UserRepository = $userRepository;
        $this->EventRepository = $eventRepository;
        $this->CampusRepository = $campusRepository;
        $this->CityRepository = $cityRepository;

    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        /* $url = $this->adminUrlGenerator
             ->setController(UserCrudController::class)
             ->generateUrl();*/

        return $this->render('admin/dashboard.html.twig', [
                'countAllUser' => $this->UserRepository->countAllUser(),
                'countAllCampus' => $this->CampusRepository->countAllCampus(),
                'countAllCity' => $this->CityRepository->countAllCity(),
                'countAllEvent' => $this->EventRepository->countAllEvent()
            ]
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('DashBoard ENI-Sortie')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud(
            'Utilisateurs',
            'fa fa-users',
            User::class
        );
        yield MenuItem::linkToCrud(
            'Campus',
            'fa fa-school',
            Campus::class
        );
        yield MenuItem::linkToCrud(
            'Villes',
            'fa fa-city',
            City::class
        );
        yield MenuItem::linkToCrud(
            'Sorties',
            'fa fa-calendar-days',
            Event::class
        );
        yield MenuItem::linkToRoute(
            'Retour au site',
            'fa fa-home',
            'app_event_list'
        );
    }
}
