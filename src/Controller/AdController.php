<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    
    /**
     * Permet d'afficher toutes les annonces
     *
     * @param AdRepository $repo
     * @return Response
     */
    #[Route('/ads', name: 'ads_index')]
    public function index(AdRepository $repo): Response
    {
        $ads = $repo->findAll();
        // dump($ads);
        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }

    #[Route("/ads/new", name:"ads_create")]
    public function create(): Response
    {
        $ad = new Ad();
        $form = $this->createForm(AnnonceType::class, $ad);

        return $this->render("ad/new.html.twig",[
            'myForm' => $form->createView()
        ]);
    }
    
    /**
     * Permet d'afficher une annonce via son slug en paramètre
     * pour faire fonctionner cette methode, je dois aller dans config/packages/doctrine.yaml ligne 26 mettre true à auto_mapping
     * @param Ad $ad
     * @return Response
     */
    #[Route('/ads/{slug}', name:"ads_show")]
    public function show(Ad $ad): Response
    {
        return $this->render("ad/show.html.twig",[
            'ad' => $ad
        ]);
    }
}