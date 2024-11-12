<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Produit;
use App\Entity\Categorie;
use App\Controller\ProduitController;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;


class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(): Response
    {
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }
    #[Route('/admin/produit/creerform', name: 'app_produit_creer_form')]
    public function creerform(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle instance de l'entité Station
        $produit = new Produit();
   
        // Création du formulaire en associant l'entité Station
        $form = $this->createForm(ProduitType::class, $produit);
   
        // Traitement de la requête HTTP
        // Cette ligne permet au formulaire de gérer les données soumises par l'utilisateur
        $form->handleRequest($request);
   
        // Vérification si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            // Cette étape est optionnelle car l'entité $station est déjà mise à jour
            $produit = $form->getData();
   
            // Préparation de l'entité pour la sauvegarde en base de données
            $entityManager->persist($produit);
   
            // Exécution de la requête pour sauvegarder l'entité
            $entityManager->flush();
   
            // Redirection vers une autre page après le succès de l'opération
            // Assurez-vous que la route 'task_success' existe
            return $this->redirectToRoute('task_success');
        }
   
        // Affichage du formulaire dans la vue Twig
        return $this->render('produit/new.html.twig', [
            // Transmission de la vue du formulaire à la template Twig
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produit/list', name: 'produit_list')]
public function listProduits(EntityManagerInterface $entityManager, ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
{
    // Récupérer toutes les stations depuis la base de données
    $produits = $produitRepository->findAll();
    $categorie = $categorieRepository ->findAll();

    // Passer les stations à la vue
    return $this->render('produit/list.html.twig', [
        'produits' => $produits,
        'categories' => $categorie,
    ]);
}
}
