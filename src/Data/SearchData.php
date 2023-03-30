<?php

namespace App\Data;

use DateTime;
use phpDocumentor\Reflection\Types\Boolean;

class SearchData
{

    /**
     * @var string
     */
    public $siteOrganisateur='';


    /**
     * @var string
     */
    public $nom = '';



    /**
     * @var boolean
     */
    public $organisateur = false;

    /**
     * @var boolean
     */
    public  $inscrit = false;

    /**
     * @var boolean
     */
    public  $nInscrit = false;

    /**
     * @var boolean
     */
    public $passees = false;

    /**
     * @var DateTime|null
     */
    public $dateDebut = null;

    /**
     * @var DateTime|null
     */
    public $dateFin = null;

    public function __construct()
    {
        $this->dateDebut = new DateTime(); // initialisation à la date courante
    }


    /*public $dateDebut = '';

    public $dateFin ='';

    public function __construct()
    {
        $this->dateDebut = date('d-m-Y'); // initialisation à la date courante
    }*/

}