<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
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
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $ad = new Ad();

        //$arrayForm = $request->request->all();

        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //dump($arrayForm['annonce']);
            $manager->persist($ad);
            $manager->flush();

            // message flash
            $this->addFlash(
                'success',
                "L'annonce <strong>".$ad->getTitle()."</strong> a bien été enregristrée!"
            );

            // redirection vers l'annonce en elle-même
            return $this->redirectToRoute('ads_show',[
                'slug' => $ad->getSlug()
            ]);

        }


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
