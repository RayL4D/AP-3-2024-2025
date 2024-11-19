<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\AvisClient;
use App\Form\AvisClientType;
use App\Repository\AvisClientRepository;

class AvisClientController extends AbstractController
{
    #[Route('/client/avisclient', name: 'app_avis_client')]
    public function index(): Response
    {
        return $this->render('avis_client/index.html.twig', [
            'controller_name' => 'AvisClientController',
        ]);
    }

    #[Route('/client/avisclientcreerform', name: 'app_avis_client_creer_form')]
    public function creerform(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle instance de l'entité Station
        $avisclient = new AvisClient();
   
        // Création du formulaire en associant l'entité Station
        $form = $this->createForm(AvisClientType::class, $avisclient);
   
        // Traitement de la requête HTTP
        // Cette ligne permet au formulaire de gérer les données soumises par l'utilisateur
        $form->handleRequest($request);
   
        // Vérification si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            // Cette étape est optionnelle car l'entité $station est déjà mise à jour
            $avisclient = $form->getData();
   
            // Préparation de l'entité pour la sauvegarde en base de données
            $entityManager->persist($avisclient);
   
            // Exécution de la requête pour sauvegarder l'entité
            $entityManager->flush();
   
            // Redirection vers une autre page après le succès de l'opération
            // Assurez-vous que la route 'task_success' existe
            return $this->redirectToRoute('app_login');
        }
   
        // Affichage du formulaire dans la vue Twig
        return $this->render('avis_client/new.html.twig', [
            // Transmission de la vue du formulaire à la template Twig
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/list', name: 'avis_list')]
    public function listProduits(EntityManagerInterface $entityManager, AvisClientRepository $avisclientRepository): Response
    {
        // Récupérer toutes les stations depuis la base de données
        $avisclient = $avisclientRepository->findAll();
    
        // Passer les stations à la vue
        return $this->render('avis_client/list.html.twig', [
            'avisclient' => $avisclient,
        ]);
    }
}

