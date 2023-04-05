<?php

namespace App\Data;

use App\Entity\Campus;
use DateTime;

class SearchData
{
//utiliser typage car 7.4 et non annotation

    public ?Campus $siteOrganisateur=null;



    public ?string $nom = null;




    public bool $organisateur = false;


    public  bool $inscrit = false;


    public  bool $nInscrit = false;


    public bool $passees = false;


    public ?DateTime $dateDebut = null;


    public ?DateTime $dateFin = null;

    public function __construct()
    {

    }



}