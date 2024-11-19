<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'user_list')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les utilisateurs
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('user/list.html.twig', [
            'users' => $users
        ]);
    }

    // Modifier les rôles d'un utilisateur
    #[Route('/user/{id}/edit-roles', name: 'user_edit_roles')]
    public function editRoles(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserRoleType::class, $user);  // Formulaire pour modifier les rôles

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde les modifications dans la base de données
            $entityManager->flush();

            // Redirection après sauvegarde
            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/modify_roles.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/admin/accueil', name: 'admin_accueil')]
    public function adminAccueil(): Response
    {
        return $this->render('user/admin/accueil.html.twig');
    }

    #[Route('/client/accueil', name: 'client_accueil')]
    public function clientAccueil(): Response
    {
        return $this->render('user/client/accueil.html.twig');
    }
    
}
