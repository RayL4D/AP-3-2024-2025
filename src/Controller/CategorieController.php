<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Categorie;
use App\Controller\CategorieController;
use App\Form\CategorieFormType;


class CategorieController extends AbstractController
{
    #[Route('/admin/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }

    #[Route('/categorie/creerform', name: 'app_categorie_creer_form')]
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
