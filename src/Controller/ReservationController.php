<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Vehicule;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
    
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException('User is not logged in.');
        }
    
        if ($this->isGranted('ROLE_ADMIN')) {

            $reservations = $reservationRepository->findAll();
        } else {
           
            $reservations = $reservationRepository->findBy(['user' => $user]);
        }
    
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }
    

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule = $reservation->getVoiture();

            if (!$vehicule->isDisponibilite()) {
                $this->addFlash('error', 'Le véhicule sélectionné n\'est pas disponible.');
                return $this->redirectToRoute('app_reservation_new');
            }

            $dateDebut = $reservation->getDateDebut();
            $dateFin = $reservation->getDateFin();
            $interval = $dateDebut->diff($dateFin);
            $nombreJours = $interval->days;
            $prixTotal = $vehicule->getPrixJournalier() * $nombreJours;
            $reservation->setPrixTotal($prixTotal);
            $reservation->setUser($this->getUser());
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule = $reservation->getVoiture();

            if (!$vehicule->isDisponibilite()) {
                $this->addFlash('error', 'Le véhicule sélectionné n\'est pas disponible.');
                return $this->redirectToRoute('app_reservation_edit', ['id' => $reservation->getId()]);
            }

            $dateDebut = $reservation->getDateDebut();
            $dateFin = $reservation->getDateFin();
            $interval = $dateDebut->diff($dateFin);
            $nombreJours = $interval->days;
            $prixTotal = $vehicule->getPrixJournalier() * $nombreJours;
            $reservation->setPrixTotal($prixTotal);
            $reservation->setUser($this->getUser());
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
