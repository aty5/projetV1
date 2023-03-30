<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\FiltrerSortieType;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function accueil(SortieRepository $sortieRepository, /*CampusRepository $campusRepository,*/ Request $request): Response
    {
        /*$siteOrganisateur = $campusRepository->listerSitesOrganisateurs();*/

        $data = new SearchData();

        $filtrerSortieForm = $this->createForm(FiltrerSortieType::class, $data);
        $filtrerSortieForm->handleRequest($request);

        //traiter le formulaire

        $sorties = $sortieRepository->listerSortiesFiltres($data);


        return  $this->render('gestion_sortie/accueil.html.twig', [
            "sorties" => $sorties,
            "filtrerSortieForm" => $filtrerSortieForm->createView(),
            /*"siteOrganisateur" => $siteOrganisateur*/
        ]);
    }
}
