<?php

namespace App\Controller;

use App\Repository\AdRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(AdRepository $adRepo, UserRepository $userRepo): Response
    {
        return $this->render('home.html.twig', [
            'ads' => $adRepo->findBestAds(3),
            'users' => $userRepo->findBestUsers(3)
        ]);
    }
}
