<?php

namespace App\Controller;

use App\Controller\api\ApiController;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils,
                          EntityManagerInterface $entityManager,
                          SortieRepository $sortieRepository,
                          ApiController $apiController): Response
    {
        if ($this->getUser()) {
            /*$apiController->majEtat($entityManager, $sortieRepository, 'Ouverte', new \DateTime(), 'Clôturée');
            $apiController->majEtat($entityManager, $sortieRepository, 'Clôturée', new \DateTime(), 'Activité en cours');
            $apiController->majEtat($entityManager, $sortieRepository, 'Activité en cours', new \DateTime(), 'Passée');*/
return $this->redirectToRoute('gestion_sortie_accueil');
        }

// get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
// last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
