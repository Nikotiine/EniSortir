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

class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator
    )
    {
    }

    #[Route('/admin', name: 'admin')]

    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(UserCrudController::class)
            ->generateUrl();

        return $this->redirect($url);

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('ENI-Sortir Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section(
            'Dashboard',
            icon: "fa fa-home"
        );
        yield MenuItem::linkToCrud(
            "Utilisateurs",
            "fa fa-users",
            User::class
        );
        yield MenuItem::linkToCrud(
            "Campus",
            "fa fa-school",
            Campus::class
        );
        yield MenuItem::linkToCrud(
            "Villes",
            "fa fa-calendar-days",
            City::class
        );
        yield MenuItem::linkToCrud(
            "Evenements",
            "fa fa-calendar-days",
            Event::class
        );
    }

}
