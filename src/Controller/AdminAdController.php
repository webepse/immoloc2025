<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminAdController extends AbstractController
{
    #[Route('/admin/ads', name: 'admin_ads_index')]
    public function index(): Response
    {
        return $this->render('admin/ad/index.html.twig', [
            'controller_name' => 'AdminAdController',
        ]);
    }
}
