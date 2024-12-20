<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    /**
     * Permet de voir le profil d'un utilisateur donné
     *
     * @param User $user
     * @return Response
     */
    #[Route('/user/{slug}', name: 'user_show')]
    public function index(User $user): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }


    /**
     * Permet d'afficher le profil de l'utilisateur connecté
     *
     * @return Response
     */
    #[Route('/account', name: "account_index")]
    #[IsGranted('ROLE_USER')]
    public function myAccount(): Response
    {
        return $this->render("user/index.html.twig",[
            'user' => $this->getUser()
        ]);
    }
}
