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
use App\Repository\StockRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

class APIController extends AbstractController
{
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
        // Décoder le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);
        
        // Vérifier si toutes les données requises sont présentes
        if (!isset($data['nom'], $data['prix'], $data['categorie_id'], $data['quantiteStock'])) {
            // Retourner une réponse d'erreur si les données sont invalides
            return new JsonResponse(['status' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // Trouver la catégorie correspondante dans la base de données
        $categorie = $categorieRepository->find($data['categorie_id']);
        if (!$categorie) {
            // Retourner une réponse d'erreur si la catégorie n'existe pas
            return new JsonResponse(['status' => 'Invalid category'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // Calculer la valeur de Y pour le produit en fonction du nombre de produits existants dans la catégorie
        $existingProducts = $produitRepository->findBy(['laCategorie' => $categorie]);
        $newY = count($existingProducts) + 1;
        
        // Créer un nouvel objet Produit et remplir ses propriétés
        $produit = new Produit();
        $produit->setNom($data['nom']);
        $produit->setPrix($data['prix']);
        $produit->setLaCategorie($categorie);
        
        // Créer un objet Stock et définir la quantité
        $stock = new Stock();
        $stock->setQuantiteStock($data['quantiteStock']);
        $produit->setLeStock($stock);
        
        // Créer un objet Emplacement et définir ses coordonnées
        $emplacement = new Emplacement();
        $emplacement->setX($categorie->getLeEmplacement()->getX());
        $emplacement->setY($newY);
        $produit->setLeEmplacement($emplacement);
        
        // Persister le nouvel objet Produit dans la base de données
        $entityManager->persist($produit);
        $entityManager->flush();
        
        // Retourner une réponse de succès
        return new JsonResponse(['status' => 'Produit ajouté avec succès'], JsonResponse::HTTP_CREATED);
    }
    
    #[Route('/api/produits/update/{id}', name: 'app_api_update_produit', methods: ['PUT'])]
    public function updateProduit(Request $request, int $id, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): JsonResponse
    {
        // Trouver le produit à mettre à jour en utilisant l'identifiant
        $produit = $entityManager->getRepository(Produit::class)->find($id);
    
        if (!$produit) {
            // Retourner une réponse d'erreur si le produit n'est pas trouvé
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Décoder le contenu JSON de la requête pour récupérer les nouvelles données
        $data = json_decode($request->getContent(), true);
    
        // Mettre à jour les propriétés du produit si de nouvelles valeurs sont fournies
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
    
        // Sauvegarder les modifications apportées au produit
        $entityManager->flush();
    
        // Retourner une réponse de succès
        return new JsonResponse(['status' => 'Produit mis à jour avec succès']);
    }
    
    #[Route('/api/produits/delete/{id}', name: 'app_api_delete_produit', methods: ['DELETE'])]
    public function deleteProduit(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Trouver le produit à supprimer
        $produit = $entityManager->getRepository(Produit::class)->find($id);
    
        if (!$produit) {
            // Retourner une réponse d'erreur si le produit n'est pas trouvé
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Supprimer le produit de la base de données
        $entityManager->remove($produit);
        $entityManager->flush();
    
        // Retourner une réponse de succès
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


    #[Route('/api/categories/update/{id}', name: 'app_api_update_categorie', methods: ['PUT'])]
    public function updateCategorie(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Trouver la catégorie à mettre à jour
        $categorie = $entityManager->getRepository(Categorie::class)->find($id);

        if (!$categorie) {
            // Retourner une erreur si la catégorie n'existe pas
            return new JsonResponse(['status' => 'Catégorie non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Décoder le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si le champ "nom" est fourni
        if (!isset($data['nom']) || empty($data['nom'])) {
            return new JsonResponse(['status' => 'Le champ "nom" est requis'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Mettre à jour le nom de la catégorie
        $categorie->setNom($data['nom']);

        // Sauvegarder les modifications
        $entityManager->flush();

        // Retourner une réponse de succès
        return new JsonResponse(['status' => 'Nom de la catégorie mis à jour avec succès']);
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
    
    #[Route('/api/orders/check', name: 'app_api_check_order', methods: ['GET'])]
    public function checkOrder(EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
    
        if (!$user) {
            return new JsonResponse(['hasOrder' => false], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
        // Rechercher une commande avec le statut "En cours de création"
        $existingOrder = $entityManager->getRepository(Commande::class)
            ->findOneBy(['leUser' => $user, 'statut' => 'En cours de création']);
    
        // Retourner une réponse indiquant si une commande existe
        return new JsonResponse(['hasOrder' => $existingOrder !== null], JsonResponse::HTTP_OK);
    }
    


    #[Route('/api/orders/{id}/add-detail', name: 'app_api_add_detail', methods: ['POST'])]
    public function addDetail(Request $request, int $id, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): JsonResponse
    {
        // Récupérer la commande par ID
        $commande = $entityManager->getRepository(Commande::class)->find($id);
    
        if (!$commande) {
            return new JsonResponse(['status' => 'Commande non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Décoder le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);
    
        // Vérifier si les données nécessaires sont présentes
        if (!isset($data['produit_id'], $data['quantite'])) {
            return new JsonResponse(['status' => 'Données invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Récupérer le produit
        $produit = $produitRepository->find($data['produit_id']);
        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Vérifier si un détail existe déjà pour ce produit dans la commande
        $detailExist = $entityManager->getRepository(Detail::class)->findOneBy([
            'laCommande' => $commande,
            'leProduit' => $produit
        ]);
    
        if ($detailExist) {
            // Si le produit existe déjà dans la commande, on met à jour la quantité
            $detailExist->setQuantiteProduit($detailExist->getQuantiteProduit() + $data['quantite']);
        } else {
            // Sinon, on crée un nouveau détail
            $detail = new Detail();
            $detail->setLaCommande($commande);
            $detail->setLeProduit($produit);
            $detail->setQuantiteProduit($data['quantite']);
            
            $entityManager->persist($detail);
        }
    
        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();
    
        return new JsonResponse(['status' => 'Produit ajouté ou quantité mise à jour dans la commande'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/orders/{id}/remove-detail', name: 'app_api_remove_detail', methods: ['POST'])]
    public function removeDetail(
        Request $request,
        int $id,
        EntityManagerInterface $entityManager,
        ProduitRepository $produitRepository
    ): JsonResponse {
        // Récupérer la commande par ID
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            return new JsonResponse(['status' => 'Commande non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Décoder le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si les données nécessaires sont présentes
        if (!isset($data['produit_id'])) {
            return new JsonResponse(['status' => 'Données invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Récupérer le produit
        $produit = $produitRepository->find($data['produit_id']);
        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Vérifier si un détail existe pour ce produit dans la commande
        $detail = $entityManager->getRepository(Detail::class)->findOneBy([
            'laCommande' => $commande,
            'leProduit' => $produit
        ]);

        if (!$detail) {
            return new JsonResponse(['status' => 'Produit non présent dans la commande'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Décrémenter la quantité ou supprimer le détail
        $currentQuantity = $detail->getQuantiteProduit();
        if ($currentQuantity > 1) {
            $detail->setQuantiteProduit($currentQuantity - 1);
        } else {
            $entityManager->remove($detail);
        }

        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();

        return new JsonResponse(['status' => 'Produit décrémenté ou supprimé de la commande'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/orders/{id}/details', name: 'app_api_get_order_details', methods: ['GET'])]
    public function getOrderDetails(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupère une commande spécifique en utilisant son ID
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        // Vérifie si la commande existe
        if (!$commande) {
            // Renvoie une réponse JSON indiquant que la commande n'a pas été trouvée
            return new JsonResponse(['status' => 'Commande non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Récupère les détails de la commande (les produits associés, quantités, etc.)
        $details = $commande->getLesDetails();

        // Transforme les détails en un tableau contenant des informations pertinentes
        $data = array_map(function ($detail) {
            return [
                'produit_id' => $detail->getLeProduit()->getId(),       // ID du produit
                'produit_nom' => $detail->getLeProduit()->getNom(),     // Nom du produit
                'quantite' => $detail->getQuantiteProduit(),            // Quantité commandée
                'prix' => $detail->getLeProduit()->getPrix(),           // Prix du produit
            ];
        }, $details->toArray());

        // Renvoie les détails de la commande sous forme de réponse JSON
        return new JsonResponse($data);
    }



    #[Route('/api/orders/current', name: 'app_api_current_order', methods: ['GET'])]
    public function getCurrentOrder(EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Récupère l'utilisateur actuellement authentifié
        $user = $security->getUser();

        // Vérifie si l'utilisateur est connecté
        if (!$user) {
            // Renvoie une réponse JSON avec une erreur d'autorisation
            return new JsonResponse(['status' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Récupère la commande "en cours de création" associée à l'utilisateur
        $currentOrder = $entityManager->getRepository(Commande::class)
            ->findOneBy(['leUser' => $user, 'statut' => 'En cours de création']);

        // Vérifie si une commande en cours existe
        if (!$currentOrder) {
            // Renvoie une réponse JSON indiquant qu'il n'y a pas de commande active
            return new JsonResponse(['status' => 'No current order'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Renvoie les informations de la commande en cours sous forme de réponse JSON
        return new JsonResponse([
            'id' => $currentOrder->getId(),                         // ID de la commande
            'date' => $currentOrder->getDate()->format('Y-m-d H:i:s'), // Date de création de la commande
            'statut' => $currentOrder->getStatut(),                 // Statut de la commande
        ]);
    }

    

    #[Route('/api/orders/validate', name: 'app_api_validate_order', methods: ['POST'])]
    public function validateOrder(EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Récupère l'utilisateur actuellement authentifié
        $user = $security->getUser();

        // Vérifie si l'utilisateur est connecté
        if (!$user) {
            // Renvoie une réponse JSON avec une erreur d'autorisation
            return new JsonResponse(['status' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Récupère la commande "en cours de création" associée à l'utilisateur
        $currentOrder = $entityManager->getRepository(Commande::class)
            ->findOneBy(['leUser' => $user, 'statut' => 'En cours de création']);

        // Vérifie si une commande en cours existe
        if (!$currentOrder) {
            // Renvoie une réponse JSON indiquant qu'il n'y a pas de commande active
            return new JsonResponse(['status' => 'No current order'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Change le statut de la commande à "Validée"
        $currentOrder->setStatut('Validée');

        // Enregistre les modifications dans la base de données
        $entityManager->flush();

        // Renvoie une réponse JSON confirmant la validation
        return new JsonResponse(['status' => 'Commande validée avec succès']);
    }

    #[Route('/api/orders/complete/{id}', name: 'app_api_complete_order', methods: ['POST'])]
    public function completeOrder(
        int $id,
        EntityManagerInterface $entityManager,
        Security $security
    ): JsonResponse {
        // Récupère l'utilisateur actuellement authentifié
        $user = $security->getUser();
    
        // Vérifie si l'utilisateur est connecté
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
        // Recherche de la commande à partir de l'ID
        $order = $entityManager->getRepository(Commande::class)->find($id);
    
        // Vérifie si la commande existe
        if (!$order) {
            return new JsonResponse(['message' => 'Commande introuvable'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Vérifie si la commande est déjà terminée
        if ($order->getStatut() === 'Terminée') {
            return new JsonResponse(['message' => 'Commande déjà terminée'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Vérifie si l'utilisateur a le droit de modifier cette commande
        if ($order->getLeUser() !== $user) {
            return new JsonResponse(['message' => 'Action non autorisée'], JsonResponse::HTTP_FORBIDDEN);
        }
    
        // Met à jour le statut et la date de la commande
        $order->setStatut('Terminée');
        $order->setDate(new \DateTime()); // Ajoute la date actuelle
        $entityManager->flush(); // Enregistre les modifications dans la base de données
    
        // Renvoie une confirmation de succès
        return new JsonResponse(['message' => 'Commande terminée avec succès']);
    }
    

    #[Route('/api/orders/user', name: 'app_api_user_orders', methods: ['GET'])]
    public function getUserOrders(EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Récupère l'utilisateur actuellement authentifié
        $user = $security->getUser();
    
        // Vérifie si l'utilisateur est connecté
        if (!$user) {
            return new JsonResponse(['status' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
        // Récupère toutes les commandes associées à l'utilisateur
        $commandes = $entityManager->getRepository(Commande::class)->findBy(['leUser' => $user]);
    
        // Prépare les données pour une réponse JSON
        $data = array_map(function ($commande) {
            return [
                'id' => $commande->getId(),
                'date' => $commande->getDate()->format('Y-m-d H:i:s'), // Date formatée
                'statut' => $commande->getStatut(),
                'details' => array_map(function ($detail) {
                    $produit = $detail->getLeProduit();
                    $stock = $produit->getLeStock();
    
                    return [
                        'produit_id' => $produit->getId(),
                        'produit_nom' => $produit->getNom(),
                        'quantite' => $detail->getQuantiteProduit(),
                        'prix' => $produit->getPrix(),
                        'stock_quantite' => $stock ? $stock->getQuantiteStock() : null, // Stock disponible
                    ];
                }, $commande->getLesDetails()->toArray())
            ];
        }, $commandes);
    
        // Renvoie les données des commandes de l'utilisateur
        return new JsonResponse($data);
    }
    

    #[Route('/api/orders/user/admin', name: 'app_api_user_orders_admin', methods: ['GET'])]
    public function getUserOrdersAdmin(EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupère toutes les commandes (tous les utilisateurs)
        $commandes = $entityManager->getRepository(Commande::class)->findAll();
    
        // Prépare les données pour une réponse JSON
        $data = array_map(function ($commande) {
            $user = $commande->getLeUser();
    
            return [
                'id' => $commande->getId(),
                'date' => $commande->getDate()->format('Y-m-d H:i:s'), // Date formatée
                'statut' => $commande->getStatut(),
                'created_by' => $user ? [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'nom' => $user->getNom(), // Nom de l'utilisateur qui a créé la commande
                ] : null,
                'details' => array_map(function ($detail) {
                    $produit = $detail->getLeProduit();
                    $stock = $produit->getLeStock();
    
                    return [
                        'produit_id' => $produit->getId(),
                        'produit_nom' => $produit->getNom(),
                        'quantite' => $detail->getQuantiteProduit(),
                        'prix' => $produit->getPrix(),
                        'stock_quantite' => $stock ? $stock->getQuantiteStock() : null, // Stock disponible
                    ];
                }, $commande->getLesDetails()->toArray()),
            ];
        }, $commandes);
    
        // Renvoie les données de toutes les commandes
        return new JsonResponse($data);
    }
    

    #[Route('/api/produits/{id}/details', name: 'app_api_get_produit_details', methods: ['GET'])]
    public function getProduitDetails(int $id, ProduitRepository $produitRepository): JsonResponse
    {
        // Rechercher le produit par son ID
        $produit = $produitRepository->find($id);

        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Récupérer les détails du produit (nom, prix, catégorie, stock, emplacement)
        $data = [
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'prix' => $produit->getPrix(),
            'categorie' => $produit->getLaCategorie() ? [
                'id' => $produit->getLaCategorie()->getId(),
                'nom' => $produit->getLaCategorie()->getNom(),
            ] : null,
            'quantiteStock' => $produit->getLeStock() ? $produit->getLeStock()->getQuantiteStock() : null,
            'emplacement' => $produit->getLeEmplacement() ? [
                'x' => $produit->getLeEmplacement()->getX(),
                'y' => $produit->getLeEmplacement()->getY(),
            ] : null,
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/stock/{id}/increment', name: 'app_api_increment_stock', methods: ['POST'])]
    public function incrementStock(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        StockRepository $stockRepository
    ): JsonResponse {
        // Récupérer l'entité Stock par son ID
        $stock = $stockRepository->find($id);

        if (!$stock) {
            return new JsonResponse(['status' => 'Stock non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Décoder les données de la requête pour obtenir la quantité à incrémenter
        $data = json_decode($request->getContent(), true);

        if (!isset($data['quantite']) || $data['quantite'] <= 0) {
            return new JsonResponse(['status' => 'Quantité invalide'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $quantiteAIncrementer = (int) $data['quantite'];

        // Incrémenter le stock
        $stock->setQuantiteStock($stock->getQuantiteStock() + $quantiteAIncrementer);

        // Sauvegarder les modifications
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'Stock incrémenté avec succès',
            'nouveauStock' => $stock->getQuantiteStock(),
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/api/stock/{id}/decrement', name: 'app_api_decrement_stock', methods: ['POST'])]
    public function decrementStock(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        StockRepository $stockRepository
    ): JsonResponse {
        // Récupérer l'entité Stock par son ID
        $stock = $stockRepository->find($id);

        if (!$stock) {
            return new JsonResponse(['status' => 'Stock non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Décoder les données de la requête pour obtenir la quantité à décrémenter
        $data = json_decode($request->getContent(), true);

        if (!isset($data['quantite']) || $data['quantite'] <= 0) {
            return new JsonResponse(['status' => 'Quantité invalide'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $quantiteADecrementer = (int) $data['quantite'];

        // Vérifier si le stock est suffisant
        $stockActuel = $stock->getQuantiteStock();

        if ($quantiteADecrementer > $stockActuel) {
            return new JsonResponse(['status' => 'Stock insuffisant'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Décrémenter le stock
        $stock->setQuantiteStock($stockActuel - $quantiteADecrementer);

        // Sauvegarder les modifications
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'Stock décrémenté avec succès',
            'nouveauStock' => $stock->getQuantiteStock(),
        ], JsonResponse::HTTP_OK);
    }


}
