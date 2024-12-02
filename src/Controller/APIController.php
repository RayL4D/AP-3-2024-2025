<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\Utils;

class APIController extends AbstractController
{
    // Route principale de l'API
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'APIController',
        ]);
    }

    // Récupère la liste des produits
    #[Route('/api/produits', name: 'app_api_produits', methods: ['GET'])]
    public function getProduits(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        // Récupère tous les produits de la base de données
        $produits = $produitRepository->findAll();
        $response = new Utils();
        // Renvoie les produits sous forme de réponse JSON
        return $response->GetJsonResponse($request, $produits);
    }

    // Ajoute un nouveau produit
    #[Route('/api/produits', name: 'ajouter_produit', methods: ['POST'])]
    public function ajouterProduit(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $nomProduit = $data['nom'] ?? null;
        $categorieId = $data['categorieId'] ?? null;
    
        if (!$nomProduit || !$categorieId) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }
    
        // Chercher la catégorie par son ID
        $categorie = $em->getRepository(Categorie::class)->find($categorieId);
        if (!$categorie) {
            return new JsonResponse(['error' => 'Catégorie introuvable'], 404);
        }
    
        // Créer un nouveau produit
        $produit = new Produit();
        $produit->setNom($nomProduit);
        $produit->setPrix($data['prix']);  // N'oubliez pas de définir le prix
        $produit->setLaCategorie($categorie);
    
        $em->persist($produit);
        $em->flush();
    
        return new JsonResponse(['message' => 'Produit ajouté avec succès'], JsonResponse::HTTP_CREATED);
    }
    
    
    
    
    // Met à jour un produit existant
    #[Route('/api/produits/update/{id}', name: 'app_api_update_produit', methods: ['PUT'])]
    public function updateProduit(Request $request, $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupère le produit à partir de la base de données
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            // Si le produit n'est pas trouvé, renvoie une réponse JSON avec un message d'erreur
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Récupère les nouvelles données de la requête
        $data = json_decode($request->getContent(), true);
        // Met à jour les propriétés du produit avec les nouvelles données
        $produit->setNom($data['nom']);
        $produit->setPrix($data['prix']);
        // Récupère et définit la catégorie associée au produit
        $produit->setLaCategorie($data['categorie']);

        // Sauvegarde les modifications dans la base de données
        $entityManager->flush();

        // Renvoie une réponse JSON indiquant que le produit a été mis à jour avec succès
        return new JsonResponse(['status' => 'Produit mis à jour avec succès']);
    }

    // Supprime un produit
    #[Route('/api/produits/delete/{id}', name: 'app_api_delete_produit', methods: ['DELETE'])]
    public function deleteProduit($id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupère le produit à partir de la base de données
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            // Si le produit n'est pas trouvé, renvoie une réponse JSON avec un message d'erreur
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Supprime le produit de la base de données
        $entityManager->remove($produit);
        $entityManager->flush();

        // Renvoie une réponse JSON indiquant que le produit a été supprimé avec succès
        return new JsonResponse(['status' => 'Produit supprimé avec succès']);
    }

    // Récupère la liste des catégories
    #[Route('/api/categories', name: 'api_categories', methods: ['GET'])]
    public function getCategories(Request $request, CategorieRepository $categorieRepository): JsonResponse
    {
        // Récupère toutes les catégories de la base de données
        $categories = $categorieRepository->findAll();
        $response = new Utils();
        // Renvoie les catégories sous forme de réponse JSON
        return $response->GetJsonResponse($request, $categories);
    }

    // Ajoute une nouvelle catégorie
    #[Route('/api/categories/add', name: 'app_api_add_categorie', methods: ['POST'])]
    public function addCategorie(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Création d'une nouvelle entité catégorie
        $categorie = new Categorie();
        $categorie->setNom($data['nom']);

        // Persist et sauvegarde la catégorie dans la base de données
        $entityManager->persist($categorie);
        $entityManager->flush();

        // Renvoie une réponse JSON indiquant que la catégorie a été ajoutée avec succès
        return new JsonResponse(['status' => 'Catégorie ajoutée avec succès'], JsonResponse::HTTP_CREATED);
    }

    // Supprime une catégorie
    #[Route('/api/categories/delete/{id}', name: 'app_api_delete_categorie', methods: ['DELETE'])]
    public function deleteCategorie($id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupère la catégorie à partir de la base de données
        $categorie = $entityManager->getRepository(Categorie::class)->find($id);

        if (!$categorie) {
            // Si la catégorie n'est pas trouvée, renvoie une réponse JSON avec un message d'erreur
            return new JsonResponse(['status' => 'Catégorie non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Supprime la catégorie de la base de données
        $entityManager->remove($categorie);
        $entityManager->flush();

        // Renvoie une réponse JSON indiquant que la catégorie a été supprimée avec succès
        return new JsonResponse(['status' => 'Catégorie supprimée avec succès']);
    }
}
