<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Categorie;
use App\Controller\CategorieController;
use App\Form\CategorieFormType;


class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les catégories
        $categories = $entityManager->getRepository(Categorie::class)->findAll();

        // Passer les catégories à la vue
        return $this->render('categorie/list.html.twig', [
            'categories' => $categories, // Passer les catégories récupérées à la vue
        ]);
    }
    #[Route('/categorie/{id}/edit', name: 'edit_categorie')]
    public function edit(Categorie $categorie, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer le formulaire pour modifier la catégorie
        $form = $this->createForm(CategorieFormType::class, $categorie);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les modifications
            $entityManager->flush();

            // Rediriger vers la liste des catégories ou vers la page de modification
            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/categorie/creerform', name: 'app_categorie_creer_form')]
 public function creerform(Request $request, EntityManagerInterface $entityManager): Response
 {
     // Création d'une nouvelle instance de l'entité Station
     $categorie = new Categorie();

     // Création du formulaire en associant l'entité Station
     $form = $this->createForm(CategorieFormType::class, $categorie);

     // Traitement de la requête HTTP
     // Cette ligne permet au formulaire de gérer les données soumises par l'utilisateur
     $form->handleRequest($request);

     // Vérification si le formulaire a été soumis et si les données sont valides
     if ($form->isSubmitted() && $form->isValid()) {
         // Récupération des données du formulaire
         // Cette étape est optionnelle car l'entité $station est déjà mise à jour
         $categorie = $form->getData();

         // Préparation de l'entité pour la sauvegarde en base de données
         $entityManager->persist($categorie);

         // Exécution de la requête pour sauvegarder l'entité
         $entityManager->flush();

         // Redirection vers une autre page après le succès de l'opération
         // Assurez-vous que la route 'task_success' existe
         return $this->redirectToRoute('task_success');
     }

     // Affichage du formulaire dans la vue Twig
     return $this->render('categorie/new.html.twig', [
         // Transmission de la vue du formulaire à la template Twig
         'form' => $form->createView(),
     ]);
 }
}
