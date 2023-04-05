<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\FiltrerSortieType;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/gestion_sortie", name="gestion_sortie_")
 */
class GestionSortieController extends AbstractController
{
    /**
     * @Route("/creer", name="creer")
     */
    public function creer(): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class,$sortie);

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class,$lieu);

        return $this->render('gestion_sortie/creer.html.twig',[
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView(),
        ]);
    }

    /**
     * @Route("/accueil", name="accueil")
     */
    public function accueil(SortieRepository $sortieRepository, Request $request): Response
    {
        $data = new SearchData();
        $data->siteOrganisateur = $this->getUser()->getCampus();

        $filtrerSortieForm = $this->createForm(FiltrerSortieType::class, $data);
        $filtrerSortieForm->handleRequest($request);

        $sorties = $sortieRepository->listerSortiesFiltres($data);


        return  $this->render('gestion_sortie/accueil.html.twig', [
            "sorties" => $sorties,
            "filtrerSortieForm" => $filtrerSortieForm->createView(),
        ]);
    }


    // requirements: sécuriser l'url en demandant un caractere numerique
    /**
     * @Route("/details/{id}", name="details", requirements={"id" = "\d+"})
     */
    public function details(int $id, SortieRepository $sortieRepository, SessionInterface $session): Response
    {
        $sortie = $sortieRepository->find($id);
        if (!$sortie) {
            $session->getFlashBag()->add('error', 'La sortie demandée n\'existe pas.');
            return new RedirectResponse($this->generateUrl('gestion_sortie_accueil'));
        }

        return $this->render('gestion_sortie/details.html.twig', [
            "sortie" => $sortie
        ]);
    }


    /**
     * @Route("/sortie/{id}/inscription", name="inscription", requirements={"id" = "\d+"})
     */
    public function inscription(?Sortie $sortie, EntityManagerInterface $entityManager, Request $request): Response //peut etre que la param converter
    {
        // Vérifier que la sortie existe
        if (!$sortie) {
            $this->addFlash('error', 'La sortie demandée n\'existe pas.');
            return $this->redirectToRoute('gestion_sortie_accueil');
        }

        // Vérifier que la sortie est ouverte
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire à cette sortie car elle n\'est pas ouverte.');
            return $this->redirectToRoute('gestion_sortie_accueil');
        }

        // Vérifier que la date limite d'inscription n'est pas passée
        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire à cette sortie car la date limite d\'inscription est passée.');
            return $this->redirectToRoute('gestion_sortie_accueil');
        }


        $participant = $this->getUser();
        $sortie->addParticipant($participant);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes bien inscrit à la sortie.');


        // Rediriger l'utilisateur vers la route d'inscription en conservant les paramètres
        return $this->redirectToRoute('gestion_sortie_accueil', $request->query->all());
    }

    /**
     * @Route("/desinscription/{id}", name="desinscription",  requirements={"id" = "\d+"})
     */
    public function desinscription(?Sortie $sortie, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Vérifier que la sortie existe
        if (!$sortie) {
            $this->addFlash('error', 'La sortie demandée n\'existe pas.');
            return $this->redirectToRoute('gestion_sortie_accueil', $request->query->all());
        }

        // Vérifier que l'état de la sortie est différent de "Activité en cours"
        if ($sortie->getEtat()->getLibelle() === 'Activité en cours') {
            $this->addFlash('error', 'Impossible de se désinscrire d\'une sortie en cours.');
            return $this->redirectToRoute('gestion_sortie_accueil', $request->query->all());
        }

        $participant = $this->getUser();
        if ($sortie->getParticipants()->contains($participant)) {
            $sortie->removeParticipant($participant);
            $entityManager->flush();
        }
        $this->addFlash('success', 'Vous êtes bien désinscrit de la sortie.');

        return $this->redirectToRoute('gestion_sortie_accueil', $request->query->all());
    }

}
