<?php

namespace App\Controller\Admin;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator
    )
    {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        /* $url = $this->adminUrlGenerator
             ->setController(UserCrudController::class)
             ->generateUrl();*/

        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('ENI-Sortie')
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
