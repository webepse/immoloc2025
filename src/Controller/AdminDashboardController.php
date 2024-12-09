<?php

namespace App\Controller;

use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard_index')]
    public function index(StatsService $statsService): Response
    {
        $users = $statsService->getUsersCount();
        $ads = $statsService->getAdsCount();
        $bookings = $statsService->getBookingsCount();
        $comments = $statsService->getCommentsCount();

        $bestAds = $statsService->getAdsStats('DESC');
        $worstAds = $statsService->getAdsStats('ASC');

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => [
                'users' => $users,
                'ads' => $ads,
                'bookings' => $bookings,
                'comments' => $comments
            ],
            'bestAds' => $bestAds,
            'worstAds' => $worstAds
        ]);
    }
}
