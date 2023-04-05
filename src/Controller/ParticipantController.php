<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/participant/{id}", name="details_participant")
     */
    public function details(int $id, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->find($id);
        if (!$participant) {
            $this->addFlash('error', 'Le participant demandé n\'existe pas.');
            return $this->redirectToRoute('gestion_sortie_accueil');
        }

        return $this->render('participant/details_participants.html.twig', [
            "participant" => $participant
        ]);
    }


}
