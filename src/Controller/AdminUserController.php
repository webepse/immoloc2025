<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    #[Route('/admin/user/{page<\d+>?1}', name: 'admin_users_index')]
    public function index(int $page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(User::class)
                ->setPage($page)
                ->setLimit(10);

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
