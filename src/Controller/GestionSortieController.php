<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Entity\Campus;
use App\Form\FiltrerSortieType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/creer/{id}", name="creer",methods={"GET","POST"})
     * @return Response
     */
    public function creer(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {

        // Récupéré le campus de l'organisateur
        $organisateur = $this->getUser();
        $organisateurCampus = $organisateur->getCampus();


        // Récupéré la liste de toutes les villes
        $villes = $entityManager->getRepository(Ville::class)->findAll();

        // Instantier une sortie et lui affecté le campus de l'organisateur comme
        if($id == -1)
        {
            $sortie = new Sortie();
            $sortie->setSiteOrganisateur($organisateurCampus);
            $villeSelectionnee = '';
            $codePostal = '';
            $rue = '';
            $latitude = '';
            $longitude = '';
        } else
        {
            $sortie = $entityManager->getRepository(Sortie::class)->find($id);
            $villeSelectionnee = $sortie->getLieu()->getVille()->getNom();
            $codePostal = $sortie->getLieu()->getVille()->getCodePostal();
            $rue = $sortie->getLieu()->getRue();
            $latitude = $sortie->getLieu()->getLatitude();
            $longitude = $sortie->getLieu()->getLongitude();

        }

        // Créer le formulaire correspondant
        $sortieForm = $this->createForm(SortieType::class,$sortie);

        // Traiter le formulaire
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted())
        {
            $sortie->setOrganisateur($organisateur);

            dump($_POST);

            if(isset($_POST['enregistrer']) && $sortieForm->isValid())
            {
                dump($sortie);

                $etat = $entityManager->getRepository(Etat::class)->findOneBy(["libelle"=>'Créée']);
                $this->gererSortie($sortie, $etat, $organisateur, $entityManager);
                $this->addFlash('succes','Nouvelle sortie créée avec succes');
                return $this->redirectToRoute('gestion_sortie_creer',['id'=>$sortie->getId()]);


            } elseif(isset($_POST['publier']))
            {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(["libelle"=>'Ouverte']);
                $this->gererSortie($sortie, $etat, $organisateur, $entityManager);
                $this->addFlash('succes','La sortie a été publiée avec succes');
                return $this->redirectToRoute('gestion_sortie_accueil');

            } elseif (isset($_POST['annuler']))
            {
                return $this->redirectToRoute('gestion_sortie_annuler',['id' => $sortie->getId()]);
            }
//            else
//            {
//                /*$etat = $entityManager->getRepository(Etat::class)->findOneBy(["libelle"=>'Annulée']);
//                $this->gererSortie($sortie, $etat, $organisateur, $entityManager);
//                $this->addFlash('succes','La sortie a été annulée avec succes');*/
//                return $this->redirectToRoute('gestion_sortie_accueil');
//            }
        }

        // Publier la page
        return $this->render('gestion_sortie/creer.html.twig',[
            'sortieForm' => $sortieForm->createView(),
            'villes' => $villes,
            'campus' => $organisateurCampus->getNom(),
            'id' => $id,
            'villeSelectionnee' => $villeSelectionnee,
            'code_postal' => $codePostal,
            'nom_de_sortie' => $sortie->getNom(),
            'rue' => $rue,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'identifiantOrganisateur' => $sortie->getOrganisateur() != null ? $sortie->getOrganisateur()->getUserIdentifier() : '',
            'identifiantUtilisateur' => $organisateur->getUserIdentifier(),
            'etat' => $sortie->getEtat() != null ? $sortie->getEtat()->getLibelle() : ''

        ]);
    }

    /**
     * @Route("/annuler/{id}", name="annuler", methods={"GET","POST"})
     * @return Response
     */
    public function annuler(int $id, EntityManagerInterface $entityManager, Request $request):Response
    {
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);
        $etatAnnulee = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']);
        $sortieForm = $this->createForm(SortieType::class,$sortie);

        $validation = true;
        dump($sortie);

        $sortieForm->handleRequest($request);

        dump($_POST);

        if($sortieForm->isSubmitted())
        {
            dump($sortie);
            if($_POST["sortie"]["motifAnnulation"] != '')
            {
                $sortie->setEtat($etatAnnulee);
                dump($sortie);
                $entityManager->persist($sortie);
                $entityManager->flush();

                $this->addFlash('succes','La sortie a été annulée avec succes');
                return $this->redirectToRoute('gestion_sortie_creer',['id'=>$id]);
            }
            else
            {
                $validation = false;
            }


        }


        return $this->render('gestion_sortie/annuler.html.twig',[
            'sortieForm' => $sortieForm->createView(),
            'id' => $id,
            'nom_sortie' => $sortie->getNom(),
            'date_sortie' => $sortie->getDateHeureDebut()->format('l j F A'),
            'lieu' => $sortie->getLieu()->getNom(),
            'ville' => $sortie->getLieu()->getVille()->getNom(),
            'validation' => $validation,
            'etat' => $sortie->getEtat()->getLibelle()
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

    public function gererSortie(Sortie $sortie, Etat $etat, Participant $organisateur, EntityManagerInterface $entityManager)
    {
        $sortie->setEtat($etat);
        $organisateur->addSortie($sortie);
        $etat->addSorties($sortie);
        $entityManager->persist($sortie);
        $entityManager->persist($etat);
        $entityManager->persist($organisateur);
        $entityManager->flush();
    }

}
