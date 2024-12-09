<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AdminAccountController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_account_login')]
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();
        $loginError = null;
        // dump($error);
        if($error instanceof TooManyLoginAttemptsAuthenticationException)
        {
            // L'erreur est due à la limitation de la connexion (login throttling)
            $loginError = "Trop de tentatives de connexion. Réessayez plus tard";
        }

        $user = $this->getUser();

        if($user)
        {
            if(in_array('ROLE_ADMIN',$user->getRoles()))
            {
                return $this->redirectToRoute("admin_dashboard_index");
            }
        }


        return $this->render('admin/account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
            'loginError' => $loginError
        ]);
    }

       /**
     * Permet de se déconnecter
     *
     * @return void
     */
    #[Route('/logout', name: 'admin_account_logout')]
    public function logout(): void{}
}
