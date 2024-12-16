<?php
// src/Controller/HomeController.php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\DetailRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();
        return $this->render('home/index.html.twig', [
            'categories' => $categories,
        ]);
    }


    #[Route('/commande', name: 'app_commande')]
    public function Commande(DetailRepository $detailRepository): Response
    {
        $commande = $detailRepository->findAll();
        return $this->render('user/commande.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/anciennecommande', name: 'app_ancienne_commande')]
    public function AncienneCommande(DetailRepository $detailRepository): Response
    {
        $commande = $detailRepository->findAll();
        return $this->render('user/anciennecommande.html.twig', [
            'commande' => $commande,
        ]);
    }
}
