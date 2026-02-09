<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Form\ProfileEditType;
use App\Form\ProfileWizardType;
use App\Repository\FollowRequestRepository;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile/wizard', name: 'app_profile_wizard')]
    public function wizard(Request $request, EntityManagerInterface $em, ProfileRepository $profileRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $profile = $profileRepo->findOneBy(['user' => $user]);

        if ($profile === null) {
            $profile = new Profile();
            $profile->setDisplayName($user->getEmail());
            $profile->setUser($user);
        }

        $form = $this->createForm(ProfileWizardType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhotoUpload($form, $profile);
            $profile->setWizardCompleted(true);
            $em->persist($profile);
            $em->flush();

            $this->addFlash('success', 'Profile completed!');

            return $this->redirectToRoute('app_users');
        }

        return $this->render('profile/wizard.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $em, ProfileRepository $profileRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $profile = $profileRepo->findOneBy(['user' => $user]);

        $form = $this->createForm(ProfileEditType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhotoUpload($form, $profile);
            $em->flush();

            $this->addFlash('success', 'Profile updated!');

            return $this->redirectToRoute('app_profile_edit');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile_show')]
    public function show(
        User $user,
        FollowRequestRepository $followRequestRepo,
        ProfileRepository $profileRepo,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $relationshipStatus = null;
        $profile = $profileRepo->findOneBy(['user' => $user]);

        if ($currentUser->getId() !== $user->getId()) {
            $relationshipStatus = $followRequestRepo->getRelationshipStatus($currentUser, $user);
        }

        return $this->render('profile/show.html.twig', [
            'profileUser' => $user,
            'profile' => $profile,
            'relationshipStatus' => $relationshipStatus,
            'isOwnProfile' => $currentUser->getId() === $user->getId(),
        ]);
    }

    private function handlePhotoUpload(mixed $form, Profile $profile): void
    {
        /** @var UploadedFile|null $photoFile */
        $photoFile = $form->get('photoFile')->getData();

        if ($photoFile !== null) {
            $filename = uniqid() . '.' . $photoFile->guessExtension();
            $photoFile->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/photos',
                $filename,
            );
            $profile->setPhotoFilename($filename);
        }
    }
}
