<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvisClientController extends AbstractController
{
    #[Route('/avis/client', name: 'app_avis_client')]
    public function index(): Response
    {
        return $this->render('avis_client/index.html.twig', [
            'controller_name' => 'AvisClientController',
        ]);
    }
}
