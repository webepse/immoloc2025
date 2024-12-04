<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * Permet d'afficher la page d'administration des annonces
     *
     * #[Route('/admin/ads/{page}', name: 'admin_ads_index', requirements:["page"=>"\d+"])]
     * #[Route('/admin/ads/{page?1}', name: 'admin_ads_index', requirements:["page"=>"\d+"])]
     * 
     * @param AdRepository $repo
     * @return Response
     */
    /* wwww.monsite.be/admin/ads/ */
    /* wwww.monsite.be/admin/ads/1 */
    #[Route('/admin/ads/{page<\d+>?1}', name: 'admin_ads_index')]
    public function index(int $page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(Ad::class)
                ->setPage($page)
                ->setLimit(9);

        return $this->render('admin/ad/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet de modifier une annonce via l'administration
     *
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/ads/{id}/edit", name: "admin_ads_edit")]
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>".$ad->getTitle()."</strong> a bien été modifiée"
            );
            return $this->redirectToRoute("admin_ads_index");
        }

        return $this->render("admin/ad/edit.html.twig",[
            'ad' => $ad,
            'myForm' => $form->createView()
        ]);
    }


    /**
     * Permet de supprimer une annonce
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("admin/ads/{id}/delete", name: "admin_ads_delete")]
    public function delete(Ad $ad, EntityManagerInterface $manager): Response
    {
        // on ne peut pas supprimer une annonce qui possède des réservations
        if(count($ad->getBookings()) > 0)
        {
            $this->addFlash(
                'warning',
                "Vous ne pouvez pas supprimer l'annonce <strong>".$ad->getTitle()."</strong> car elle possède des réservations"
            );
        }else{
            $this->addFlash(
                'success',
                "L'annonce  <strong>".$ad->getTitle()."</strong> a bein été supprimée"
            );
            $manager->remove($ad);
            $manager->flush();
        }

        return $this->redirectToRoute('admin_ads_index');

    }

}
