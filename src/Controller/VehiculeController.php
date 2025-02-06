<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Commentaires;
use App\Form\CommentaireType;


#[Route('/vehicule')]
final class VehiculeController extends AbstractController
{
    #[Route(name: 'app_vehicule_index', methods: ['GET'])]
public function index(Request $request, VehiculeRepository $vehiculeRepository): Response
{
    $marque = $request->query->get('marque');
    $prixMax = $request->query->get('prix_max');
    $disponibilite = $request->query->get('disponibilite');
    $vehicules = $vehiculeRepository->findByFilters($marque, $prixMax, $disponibilite);

    // Calculer la moyenne des notes pour chaque véhicule
    $vehiculesAvecNotes = [];
    foreach ($vehicules as $vehicule) {
        $commentaires = $vehicule->getCommentaires();
        $moyenneNotes = $commentaires->isEmpty() ? 0 : array_reduce($commentaires->toArray(), function($carry, $commentaire) {
            return $carry + $commentaire->getNote();
        }, 0) / $commentaires->count();

        $vehiculesAvecNotes[] = [
            'vehicule' => $vehicule,
            'moyenneNotes' => $moyenneNotes,
        ];
    }

    return $this->render('vehicule/index.html.twig', [
        'vehiculesAvecNotes' => $vehiculesAvecNotes,
    ]);
}

    #[Route('/new', name: 'app_vehicule_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehicule = new Vehicule();
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vehicule);
            $entityManager->flush();

            return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vehicule/new.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vehicule_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Vehicule $vehicule, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaires();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setVoiture($vehicule);
            $entityManager->persist($commentaire);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_vehicule_show', ['id' => $vehicule->getId()]);
        }
    
        $commentaires = $vehicule->getCommentaires();
        $moyenneNotes = $commentaires->isEmpty() ? 0 : array_reduce($commentaires->toArray(), function($carry, $commentaire) {
            return $carry + $commentaire->getNote();
        }, 0) / $commentaires->count();
    
        return $this->render('vehicule/show.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form->createView(),
            'commentaires' => $commentaires,
            'moyenneNotes' => $moyenneNotes,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vehicule_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vehicule $vehicule, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vehicule/edit.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vehicule_delete', methods: ['POST'])]
    public function delete(Request $request, Vehicule $vehicule, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vehicule->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($vehicule);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_vehicule_index', [], Response::HTTP_SEE_OTHER);
    }
}
