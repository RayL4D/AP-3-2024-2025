<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Categorie;
use App\Form\CategorieFormType;
use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CategorieController extends AbstractController
{
    #[Route('/admin/categorie/list', name: 'categorie_list')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les catégories
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        // Passer les catégories à la vue
        return $this->render('categorie/list.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/admin/categorie/{id}/edit', name: 'edit_categorie')]
    public function edit(Categorie $categorie, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieFormType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('categorie_list');
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/categorie/{id}/delete', name: 'delete_categorie', methods: ['POST'])]
    public function delete(Categorie $categorie, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Supprimer la catégorie de la base de données
        $entityManager->remove($categorie);
        $entityManager->flush();

        // Rediriger vers la liste des catégories après la suppression
        return $this->redirectToRoute('categorie_list');
    }

    #[Route('/admin/categorie/creerform', name: 'app_categorie_creer_form')]
    public function creerform(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieFormType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('categorie_list');
        }

        return $this->render('categorie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories', name: 'app_categorie_client')]
    public function showCategories(Request $request, CategorieRepository $categorieRepository): Response
    {
        // Récupérer le critère de tri depuis la requête (par défaut 'p.nom')
        $sort = $request->query->get('sort', 'p.nom'); // Par exemple, trier par produit nom

        // Récupérer toutes les catégories avec leurs produits triés
        $categories = $categorieRepository->findAllWithProduits($sort);

        // Passer les catégories et leurs produits à la vue
        return $this->render('categorie/show.html.twig', [
            'categories' => $categories,
            'sort' => $sort,  // Passer le critère de tri pour le conserver dans l'URL
        ]);
    }
}
