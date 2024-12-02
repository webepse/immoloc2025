<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    #[Route('/ads/{slug}/book', name: 'booking_create')]
    #[IsGranted("ROLE_USER")]
    public function book(Ad $ad, EntityManagerInterface $manager, Request $request): Response
    {
        $booking = new Booking();
        // pour choisir les gruops de validation on peut passer par le controller (attention si on donne juste un groupe de validation les assert par défaut ne sont plus prisent en compte sauf si on ajoute le groupe Default)
        // $form = $this->createForm(BookingType::class,$booking,[
        //     "validation_groups" => ["Default","front"]
        // ]);
        $form = $this->createForm(BookingType::class,$booking);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // traitement -> ajouter le user et l'annonce à l'objet $booking
            $user = $this->getUser();
            $booking->setBooker($user)
                ->setAd($ad);

            if(!$booking->isBookableDates())
            {
                $this->addFlash(
                    'warning',
                    "Les dates que vous avez choisie ne peuvent être réservée: elles sont déjà prises!"
                );
            }else{
             
                $manager->persist($booking);
                $manager->flush();

                // redirection vers 
                return $this->redirectToRoute('booking_show',[
                    'id' => $booking->getId(),
                    'withAlert' => true
                ]);
            }

        }

        return $this->render('booking/book.html.twig', [
            'ad' => $ad,
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher la page d'une réservation
     *
     * @param Booking $booking
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/booking/{id}", name:"booking_show")]
    #[IsGranted("ROLE_USER")]
    public function show(Booking $booking, Request $request, EntityManagerInterface $manager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setAd($booking->getAd())
                ->setAuthor($this->getUser());

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success',"Votre commentaire a bien été pris en compte");
        }

        return $this->render("booking/show.html.twig",[
            'booking' => $booking,
            'myForm' => $form->createView()
        ]);
    }
}
