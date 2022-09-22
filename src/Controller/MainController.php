<?php

namespace App\Controller;

use App\Service\ChartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/accueil', name: 'app_main')]
    public function index(ChartService $chartService): Response {

        $charts = $chartService->polarAreaChart();
       return $this->render('main/home.html.twig', [
           'chart' => $charts,
        ]);
   }
}
