<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\Stock;
use App\Entity\Emplacement;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class APIController extends AbstractController
{
    private int $categorieCounter = 1; // Compteur pour l'assignation des valeurs x
    private int $produitCounter = 1;   // Compteur pour l'assignation des valeurs y

    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'APIController',
        ]);
    }

    #[Route('/api/produits', name: 'app_api_produits', methods: ['GET'])]
    public function getProduits(ProduitRepository $produitRepository): JsonResponse
    {
        $produits = $produitRepository->findAll();
        $data = array_map(function($produit) {
            return [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'categorie_id' => $produit->getLaCategorie() ? $produit->getLaCategorie()->getId() : null,
                'quantiteStock' => $produit->getLeStock() ? $produit->getLeStock()->getQuantiteStock() : null,
                'x' => $produit->getLeEmplacement() ? $produit->getLeEmplacement()->getX() : null,
                'y' => $produit->getLeEmplacement() ? $produit->getLeEmplacement()->getY() : null,
            ];
        }, $produits);

        return new JsonResponse($data);
    }

    #[Route('/api/produits/add', name: 'app_api_add_produit', methods: ['POST'])]
    public function addProduit(Request $request, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository, ProduitRepository $produitRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['nom'], $data['prix'], $data['categorie_id'], $data['quantiteStock'])) {
            return new JsonResponse(['status' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $categorie = $categorieRepository->find($data['categorie_id']);
        if (!$categorie) {
            return new JsonResponse(['status' => 'Invalid category'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Calculer la valeur de y pour le produit
        $existingProducts = $produitRepository->findBy(['laCategorie' => $categorie]);
        $newY = count($existingProducts) + 1;
    
        $produit = new Produit();
        $produit->setNom($data['nom']);
        $produit->setPrix($data['prix']);
        $produit->setLaCategorie($categorie);
    
        $stock = new Stock();
        $stock->setQuantiteStock($data['quantiteStock']);
        $produit->setLeStock($stock);
    
        $emplacement = new Emplacement();
        $emplacement->setX($categorie->getLeEmplacement()->getX());
        $emplacement->setY($newY);
        $produit->setLeEmplacement($emplacement);
    
        $entityManager->persist($produit);
        $entityManager->flush();
    
        return new JsonResponse(['status' => 'Produit ajouté avec succès'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/produits/update/{id}', name: 'app_api_update_produit', methods: ['PUT'])]
    public function updateProduit(Request $request, int $id, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): JsonResponse
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $produit->setNom($data['nom']);
        }

        if (isset($data['prix'])) {
            $produit->setPrix($data['prix']);
        }

        if (isset($data['categorie_id'])) {
            $categorie = $categorieRepository->find($data['categorie_id']);
            if ($categorie) {
                $produit->setLaCategorie($categorie);
            }
        }

        if (isset($data['quantiteStock'])) {
            $produit->getLeStock()->setQuantiteStock($data['quantiteStock']);
        }

        if (isset($data['x']) && isset($data['y'])) {
            $produit->getLeEmplacement()->setX($data['x']);
            $produit->getLeEmplacement()->setY($data['y']);
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'Produit mis à jour avec succès']);
    }

    #[Route('/api/produits/delete/{id}', name: 'app_api_delete_produit', methods: ['DELETE'])]
    public function deleteProduit(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($produit);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Produit supprimé avec succès']);
    }
    // Récupère la liste des catégories
    #[Route('/api/categories', name: 'api_categories', methods: ['GET'])]
    public function getCategories(CategorieRepository $categorieRepository): JsonResponse
    {
        $categories = $categorieRepository->findAll();
        $data = array_map(function($categorie) {
            return [
                'id' => $categorie->getId(),
                'nom' => $categorie->getNom(),
                'x' => $categorie->getLeEmplacement() ? $categorie->getLeEmplacement()->getX() : null,
                'produits' => array_map(function($produit) {
                    return [
                        'id' => $produit->getId(),
                        'nom' => $produit->getNom(),
                        'prix' => $produit->getPrix(),
                    ];
                }, $categorie->getLesProduits()->toArray())
            ];
        }, $categories);
    
        return new JsonResponse($data);
    }

    #[Route('/api/categories/add', name: 'app_api_add_categorie', methods: ['POST'])]
    public function addCategorie(Request $request, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['nom'])) {
            return new JsonResponse(['status' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Calculer la prochaine valeur de x en comptant les catégories existantes
        $existingCategories = $categorieRepository->findAll();
        $nextX = count($existingCategories) + 1;
    
        $categorie = new Categorie();
        $categorie->setNom($data['nom']);
    
        $emplacement = new Emplacement();
        $emplacement->setX($nextX);
        $emplacement->setY(0);
        $categorie->setLeEmplacement($emplacement);
    
        $entityManager->persist($categorie);
        $entityManager->flush();
    
        return new JsonResponse(['status' => 'Catégorie ajoutée avec succès'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/categories/delete/{id}', name: 'app_api_delete_categorie', methods: ['DELETE'])]
    public function deleteCategorie(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $categorie = $entityManager->getRepository(Categorie::class)->find($id);
    
        if (!$categorie) {
            return new JsonResponse(['status' => 'Catégorie non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        $entityManager->remove($categorie);
        $entityManager->flush();
    
        return new JsonResponse(['status' => 'Catégorie supprimée avec succès']);
    }
}
