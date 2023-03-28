<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function lister(): Response
    {
        return  $this->render('gestion_sortie/accueil.html.twig');
    }
}
