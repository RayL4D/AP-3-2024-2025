<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Produit;
use App\Entity\Categorie;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit = $form->getData();
            $entityManager->persist($produit);
            $entityManager->flush();
            return $this->redirectToRoute('produit_list');
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/produit/list', name: 'produit_list')]
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
#[Route('/produit/{id}/edit', name: 'edit_produit')]
public function edit(Produit $produit, Request $request, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('produit_list');
    }

    return $this->render('produit/edit.html.twig', [
        'produits' => $produit,
        'form' => $form->createView(),
    ]);
}

#[Route('/produit/{id}/delete', name: 'delete_produit', methods: ['POST'])]
public function delete(Produit $produit, EntityManagerInterface $entityManager): RedirectResponse
{
    // Supprimer la catégorie de la base de données
    $entityManager->remove($produit);
    $entityManager->flush();

    // Rediriger vers la liste des catégories après la suppression
    return $this->redirectToRoute('produit_list');
}

}
