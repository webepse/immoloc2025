<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;

class AdController extends AbstractController
{
    
    /**
     * Permet d'afficher toutes les annonces
     *
     * @param AdRepository $repo
     * @return Response
     */
    #[Route('/ads/{page<\d+>?1}', name: 'ads_index')]
    public function index(int $page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(Ad::class)
                ->setPage($page)
                ->setLimit(9);
       
        return $this->render('ad/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet de créer une annonce
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/ad/new", name:"ads_create")]
    #[IsGranted("ROLE_USER")]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $ad = new Ad();
        $user = $this->getUser();

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
            $ad->setAuthor($user);

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


    #[Route("ad/{slug}/edit", name:"ads_edit")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_USER")) or is_granted("ROLE_ADMIN")'),
        subject: new Expression('args["ad"].getAuthor()'),
        message: "Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier"

    )]
    public function edit(Request $request, EntityManagerInterface $manager, Ad $ad): Response
    {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // gestion des image
            foreach($ad->getImages() as $image)
            {
                $image->setAd($ad);
                $manager->persist($image);
            }
            $ad->setSlug("");

            $manager->persist($ad); // pas obligatoire en cas d'update
            $manager->flush();
            $this->addFlash(
                'warning',
                "L'annonce <strong>".$ad->getTitle()."</strong> a bien été modifiée"
            );

            return $this->redirectToRoute('ads_show',[
                'slug' => $ad->getSlug()
            ]);

        }


        return $this->render("ad/edit.html.twig",[
            'myForm' => $form->createView(),
            'ad' => $ad
        ]);
    }

      /**
     * Permet de supprimer une annonce
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_USER")) or is_granted("ROLE_ADMIN")'),
        subject: new Expression('args["ad"].getAuthor()'),
        message: "Cette annonce ne vous appartient pas, vous ne pouvez pas la supprimer"

    )]
    #[Route("/ad/{slug}/delete", name: "ads_delete")]
    public function delete(Ad $ad, EntityManagerInterface $manager): Response
    {
        // on ne peut pas supprimer une annonce qui possède des réservations
        if(count($ad->getBookings()) > 0)
        {
            $this->addFlash(
                'warning',
                "Vous ne pouvez pas supprimer l'annonce <strong>".$ad->getTitle()."</strong> car elle possède des réservations"
            );
            return $this->redirectToRoute('ads_show',['slug' => $ad->getSlug()]);
        }else{
            $this->addFlash(
                'success',
                "L'annonce <strong>".$ad->getTitle()."</strong> a bien été supprimée"
            );
            $manager->remove($ad);
            $manager->flush();
            return $this->redirectToRoute('ads_index');
        }


    }

    
    /**
     * Permet d'afficher une annonce via son slug en paramètre
     * je dois aller dans config/packages/doctrine.yaml et remodifier la ligne 26 mettre false à auto_mapping (valeur par défaut) mais le faire pour toutes les routes ayant un paramètre qui utilise le paramConverter
     * @param Ad $ad
     * @return Response
     */
    #[Route('/ad/{slug}', name:"ads_show")]
    public function show( 
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Ad $ad): Response
    {
        // dump($ad);
        return $this->render("ad/show.html.twig",[
            'ad' => $ad
        ]);
    }  
}
