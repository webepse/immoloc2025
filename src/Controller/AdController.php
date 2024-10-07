<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Repository\AdRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    // #[Route('/ads', name: 'ads_index')]
    // public function index(ManagerRegistry $doctrine): Response
    // {
    //     $repo = $doctrine->getRepository(Ad::class);

    //     $ads = $repo->findAll();

    //     dump($ads);

    //     return $this->render('ad/index.html.twig', [
    //         'controller_name' => 'AdController',
    //     ]);
    // }

    #[Route('/ads', name: 'ads_index')]
    public function index(AdRepository $repo): Response
    {
        $ads = $repo->findAll();
        // dump($ads);
        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }
}
