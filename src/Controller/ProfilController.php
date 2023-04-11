<?php

namespace App\Controller;

use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil/{id}", name="app_profil")
     */
    public function modifierProfil(Request $request, $id, UserPasswordHasherInterface $userPasswordHasher , EntityManagerInterface $entityManager, ParticipantRepository $participantRepository): Response
    {

        $id1 = $this->getUser()->getUserIdentifier();
        $id2 = $participantRepository->find($id)->getUserIdentifier();

        if ($id1 === $id2) {

            $user = $participantRepository->find($id);
            $form = $this->createForm(ParticipantType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setMotPasse(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $entityManager->flush();
                $this->addFlash('success', 'Profil modifié avec succes!');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('profil/monProfilForm.html.twig', [
                'user' => $user,
                'profilForm' => $form->createView()
            ]);
        } else {
            $this->addFlash('error', 'Vous ne pouvez pas acceder a cette page');
            return $this->redirectToRoute('app_login');
        }

    }
}
