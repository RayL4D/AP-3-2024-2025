<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\Stock;
use App\Entity\Emplacement;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\DetailRepository;
use App\Repository\StockRepository;
use App\Repository\EmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commande;
use App\Entity\Detail;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Data;
use Psr\Log\LoggerInterface;
use App\Http\Controllers\PanierController;






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
        $data = new Data();
        return $this->json($data);
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


    #[Route('/api/panier/ajouter', name: 'api_panier_ajouter', methods: ['POST'])]
    public function ajouterProduit(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'produitId' => 'required|integer|exists:produits,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Récupérer le produit
        $produit = Produit::find($validated['produitId']);

        // Récupérer la quantité demandée
        $quantity = $validated['quantity'];

        // Vérifier si le panier existe déjà dans la session
        $panier = Session::get('panier', []);

        // Ajouter le produit au panier ou augmenter sa quantité
        if (isset($panier[$produit->id])) {
            $panier[$produit->id]['quantity'] += $quantity; // Augmenter la quantité si déjà présent
        } else {
            // Ajouter le produit dans le panier avec sa quantité
            $panier[$produit->id] = [
                'produit' => $produit,
                'quantity' => $quantity,
            ];
        }

        // Sauvegarder les modifications dans la session
        Session::put('panier', $panier);

        // Retourner une réponse
        return response()->json([
            'status' => 'Produit ajouté au panier.',
            'panier' => $panier
        ]);
    }

    #[Route('/api/commande', name: 'api_commande', methods: ['POST'])]
    public function passerCommande(Request $request): JsonResponse
    {
        // Logique pour traiter la commande, comme l'enregistrement dans la base de données
        return new JsonResponse(['message' => 'Commande passée avec succès'], JsonResponse::HTTP_CREATED);
    }
    
    
   
}
