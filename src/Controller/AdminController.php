<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategorieRepository;


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

    #[Route('/admin/categories', name: 'app_admin_categorie')]
    public function getCategories(): Response
    {

        return $this->render('admin/categorie.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/client/produit', name: 'app_produit_client')]
    public function produitClient(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();
        return $this->render('home/produit.html.twig', [
            'categories' => $categories,
        ]);
    }
}
