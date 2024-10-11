<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
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

    /**
     * Permet de créer une annonce
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/ads/new", name:"ads_create")]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $ad = new Ad();

        // // c'est pour voir les form images 
        // $image1 = new Image();
        // $image1->setUrl("https://picsum.photos/400/200")
        //     ->setCaption('Titre 1');
        // $ad->addImage($image1);
        
        // $image2 = new Image();
        // $image2->setUrl("https://picsum.photos/400/200")
        //     ->setCaption('Titre 2');
        // $ad->addImage($image2);


        //$arrayForm = $request->request->all();

        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // gestion des images
            foreach($ad->getImages() as $image)
            {
                $image->setAd($ad);
                $manager->persist($image);
            }


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
