<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * Permet d'afficher l'ensemble via pagination des Users
     *
     * @param integer $page
     * @param PaginationService $pagination
     * @return Response
     */
    #[Route('/admin/users/{page<\d+>?1}', name: 'admin_users_index')]
    public function index(int $page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(User::class)
                ->setPage($page)
                ->setLimit(10);

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet de modifier un utilisateur
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/user/{id}/edit", name: "admin_users_edit")]
    public function edit(User $user,Request $request,EntityManagerInterface $manager): Response
    {
        $form = $this->createform(AdminUserType::class, $user);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'utilisateur n°".$user->getId()." a bien été modifié "
            );

            return $this->redirectToRoute('admin_users_index');

        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'myForm' => $form->createView()
        ]);
    }
}
