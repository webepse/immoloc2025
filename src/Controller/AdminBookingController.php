<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    /**
     * Permet d'afficher les réservations
     *
     * @param BookingRepository $repo
     * @return Response
     */
    #[Route('/admin/bookings/{page<\d+>?1}', name: 'admin_bookings_index')]
    public function index(int $page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(Booking::class)
                ->setPage($page)
                ->setLimit(9);

        return $this->render('admin/booking/index.html.twig', [
            'pagination' => $pagination
        ]);
    }


    
    /**
     * Permet de modifier une réservation
     *
     * @param Booking $booking
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/bookings/{id}/edit", name: "admin_bookings_edit")]
    public function edit(Booking $booking, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdminBookingType::class, $booking);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $booking->setAmount(0); // 0 = empty -> donc la fonction dans PrePresist de l'entity Booking va s'activer
            $manager->persist($booking);
            $manager->flush();
            
            $this->addFlash(
                'success',
                "La réservation n°<strong>".$booking->getId()."</strong> a bien été modifiée"
            );
            return $this->redirectToRoute("admin_bookings_index");
        }

        return $this->render("admin/booking/edit.html.twig",[
            'booking' => $booking,
            "myForm" => $form->createView()
        ]);
    }


    /**
     * Permet de supprimer une réservation
     *
     * @param Booking $booking
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/bookings/{id}/delete", name:"admin_bookings_delete")]
    public function delete(Booking $booking, EntityManagerInterface $manager): Response
    {
        $this->addFlash(
            'success',
            "La réservation n°<strong>".$booking->getId()."</strong> a bien été supprimée"
        );
        $manager->remove($booking);
        $manager->flush();
        return $this->redirectToRoute("admin_bookings_index");

    }
}
