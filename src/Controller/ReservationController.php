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
    
        $prixTotal = 0;
    
        if ($form->isSubmitted()) {
            $vehicule = $reservation->getVoiture();
    
            if ($vehicule) {
                $dateDebut = $reservation->getDateDebut();
                $dateFin = $reservation->getDateFin();
    
                if ($dateDebut && $dateFin) {
                    $interval = $dateDebut->diff($dateFin);
                    $nombreJours = $interval->days;
                    $prixTotal = $vehicule->getPrixJournalier() * $nombreJours;
    
                    // Vérification du prix total
                    if ($prixTotal < 100 || $prixTotal > 500) {
                        $this->addFlash('error', 'Le prix total de la réservation doit être compris entre 100 et 500.');
                        return $this->redirectToRoute('app_reservation_new');
                    }
    
                    // Application de la réduction de 10 % si le prix atteint 400 €
                    if ($prixTotal >= 400) {
                        $prixTotal = $prixTotal * 0.9;
                    }
    
                    $reservation->setPrixTotal($prixTotal);
                }
            }
    
            if ($form->isValid()) {
                if (!$vehicule->isDisponibilite()) {
                    $this->addFlash('error', 'Le véhicule sélectionné n\'est pas disponible.');
                    return $this->redirectToRoute('app_reservation_new');
                }
    
                $reservation->setUser($this->getUser());
                $entityManager->persist($reservation);
                $entityManager->flush();
    
                return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
            }
        }
    
        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
            'prixTotal' => $prixTotal,
        ]);
    }
    
    

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
    
        $prixTotal = $reservation->getPrixTotal(); // Initialiser avec le prix actuel
    
        if ($form->isSubmitted()) {
            $vehicule = $reservation->getVoiture();
    
            if ($vehicule) {
                $dateDebut = $reservation->getDateDebut();
                $dateFin = $reservation->getDateFin();
    
                if ($dateDebut && $dateFin) {
                    $interval = $dateDebut->diff($dateFin);
                    $nombreJours = $interval->days;
                    $prixTotal = $vehicule->getPrixJournalier() * $nombreJours;
    
                    // Vérification du prix total
                    if ($prixTotal < 100 || $prixTotal > 500) {
                        $this->addFlash('error', 'Le prix total de la réservation doit être compris entre 100 et 500.');
                        return $this->redirectToRoute('app_reservation_edit', ['id' => $reservation->getId()]);
                    }
    
                    // Application de la réduction de 10 % si le prix atteint 400 €
                    if ($prixTotal >= 400) {
                        $prixTotal = $prixTotal * 0.9;
                    }
    
                    $reservation->setPrixTotal($prixTotal);
                }
            }
    
            if ($form->isValid()) {
                if (!$vehicule->isDisponibilite()) {
                    $this->addFlash('error', 'Le véhicule sélectionné n\'est pas disponible.');
                    return $this->redirectToRoute('app_reservation_edit', ['id' => $reservation->getId()]);
                }
    
                $entityManager->flush();
    
                return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
            }
        }
    
        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
            'prixTotal' => $prixTotal,
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
