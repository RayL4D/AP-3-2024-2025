<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $admin = ["ROLE_ADMIN","ROLE_CLIENT","ROLE_USER"];         
        $client = ["ROLE_CLIENT","ROLE_USER"];
        $user = [];

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/produits', name: 'app_admin_produit')]
    public function getProduits(): Response
    {

        return $this->render('admin/produit.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }


    #[Route('/admin/commande', name: 'app_admin_commande')]
    public function getLesCommandesAdmin(): Response
    {

        return $this->render('admin/commande.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/categories', name: 'app_admin_categorie')]
    public function getCategories(): Response
    {

        return $this->render('admin/categorie.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/map', name: 'app_admin_map')]
    public function getMap(): Response
    {

        return $this->render('admin/map.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
