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
use App\Entity\Commande;
use App\Entity\Detail;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

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


    #[Route('/api/orders', name: 'app_api_create_order', methods: ['POST'])]
    public function createOrder(Request $request, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
    
        if (!$user) {
            // Si aucun utilisateur n'est connecté, renvoyer une erreur
            return new JsonResponse(['status' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
        // Décoder le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);
    
        // Vérifier que les données nécessaires sont présentes
        if (!isset($data['date'], $data['statut'])) {
            return new JsonResponse(['status' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Créer une nouvelle commande
        $commande = new Commande();
        $commande->setDate(new \DateTime($data['date']));
        $commande->setStatut($data['statut']);
        $commande->setLeUser($user); // Associer l'utilisateur connecté à la commande
    
        // Enregistrer la commande dans la base de données
        $entityManager->persist($commande);
        $entityManager->flush();
    
        return new JsonResponse(['status' => 'Commande créée avec succès'], JsonResponse::HTTP_CREATED);
    }
    
    #[Route('/api/commande/ajouter', name: 'api_panier_ajouter', methods: ['POST'])]
    public function ajouterCommande(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
    // Récupérer les données de la requête
    $produits = json_decode($request->getContent(), true)['produits']; // Liste des produits avec leur quantité

    // Créer une nouvelle commande
    $commande = new Commande();
    $commande->setDate(new \DateTime()); // Vous pouvez ajouter un champ Date ou Statut selon votre modèle
    $entityManager->persist($commande);
    $entityManager->flush(); // Sauvegarder la commande pour obtenir son ID

    // Ajouter les détails de commande
    foreach ($produits as $produitData) {
        $produit = $entityManager->getRepository(Produit::class)->find($produitData['id']);
        $quantite = $produitData['quantite'];

        if ($produit) {
            // Créer un détail de commande pour chaque produit
            $detailCommande = new Detail();
            $detailCommande->setLeProduit($produit);
            $detailCommande->setLaCommande($commande);
            $detailCommande->setQuantiteProduit($quantite);

            $entityManager->persist($detailCommande);
        }
    }

    // Sauvegarder les détails de commande
    $entityManager->flush();

    return new JsonResponse(['message' => 'Commande et détails ajoutés avec succès'], JsonResponse::HTTP_CREATED);
    }


    #[Route('/api/commande/{id}/ajouter-details', name: 'api_commande_ajouter_details', methods: ['POST'])]
    public function ajouterDetailsCommande(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository
    ): JsonResponse {
        // Rechercher la commande par son ID
        $commande = $commandeRepository->find($id);
    
        if (!$commande) {
            return new JsonResponse([
                'message' => 'Commande introuvable'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Décoder le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['details']) || !is_array($data['details'])) {
            return new JsonResponse([
                'message' => 'Les détails sont invalides ou manquants'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Ajouter les détails un par un
        foreach ($data['details'] as $detailData) {
            // Vérifier les informations nécessaires dans chaque détail
            if (!isset($detailData['produit'], $detailData['quantite'], $detailData['prix_unitaire'])) {
                return new JsonResponse([
                    'message' => 'Détail incomplet, produit, quantité et prix requis'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
    
            // Créer un nouveau détail
            $detail = new DetailCommande();
            $detail->setLaCommande($commande); // Associer au parent
            $detail->setLeProduit($detailData['produit']);
            $detail->setLaQuantite($detailData['quantite']);
            $detail->setLePrixUnitaire($detailData['prix_unitaire']);
    
            // Enregistrer le détail
            $entityManager->persist($detail);
        }
    
        // Sauvegarder tous les détails dans la base de données
        $entityManager->flush();
    
        return new JsonResponse([
            'message' => 'Détails ajoutés avec succès à la commande',
            'commande_id' => $commande->getId(),
            'details' => $data['details'] // Retourner les détails ajoutés pour information
        ], JsonResponse::HTTP_OK);
    }
    
    

    
}
