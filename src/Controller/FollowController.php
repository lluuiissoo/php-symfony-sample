<?php

namespace App\Controller;

use App\Entity\FollowRequest;
use App\Entity\User;
use App\Repository\FollowRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FollowController extends AbstractController
{
    #[Route('/follow/{userId}', name: 'app_follow_request', methods: ['POST'])]
    public function request(
        int $userId,
        UserRepository $userRepo,
        FollowRequestRepository $followRequestRepo,
        EntityManagerInterface $em,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $targetUser = $userRepo->find($userId);

        if ($targetUser === null) {
            throw $this->createNotFoundException('User not found.');
        }

        if ($currentUser->getId() === $targetUser->getId()) {
            $this->addFlash('error', 'You cannot follow yourself.');

            return $this->redirectToRoute('app_users');
        }

        $existingPending = $followRequestRepo->findPendingBetween($currentUser, $targetUser);
        if ($existingPending !== null) {
            $this->addFlash('info', 'Follow request already pending.');

            return $this->redirectToRoute('app_profile_show', ['id' => $userId]);
        }

        $existingApproved = $followRequestRepo->findApprovedBetween($currentUser, $targetUser);
        if ($existingApproved !== null) {
            $this->addFlash('info', 'You are already following this user.');

            return $this->redirectToRoute('app_profile_show', ['id' => $userId]);
        }

        $followRequest = new FollowRequest();
        $followRequest->setRequester($currentUser);
        $followRequest->setTarget($targetUser);

        $em->persist($followRequest);
        $em->flush();

        $this->addFlash('success', 'Follow request sent!');

        return $this->redirectToRoute('app_profile_show', ['id' => $userId]);
    }

    #[Route('/unfollow/{userId}', name: 'app_unfollow', methods: ['POST'])]
    public function unfollow(
        int $userId,
        UserRepository $userRepo,
        FollowRequestRepository $followRequestRepo,
        EntityManagerInterface $em,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $targetUser = $userRepo->find($userId);

        if ($targetUser === null) {
            throw $this->createNotFoundException('User not found.');
        }

        $approved = $followRequestRepo->findApprovedBetween($currentUser, $targetUser);
        if ($approved !== null) {
            $em->remove($approved);
            $em->flush();
            $this->addFlash('success', 'Unfollowed successfully.');
        }

        return $this->redirectToRoute('app_profile_show', ['id' => $userId]);
    }

    #[Route('/follow-requests', name: 'app_follow_requests')]
    public function pendingList(FollowRequestRepository $followRequestRepo): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $pendingRequests = $followRequestRepo->findPendingForTarget($currentUser);

        return $this->render('follow/pending.html.twig', [
            'requests' => $pendingRequests,
        ]);
    }

    #[Route('/follow-requests/{id}/approve', name: 'app_follow_approve', methods: ['POST'])]
    public function approve(
        FollowRequest $followRequest,
        EntityManagerInterface $em,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($followRequest->getTarget()?->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You can only approve your own requests.');
        }

        $followRequest->setStatus(FollowRequest::STATUS_APPROVED);
        $followRequest->setResolvedAt(new \DateTimeImmutable());
        $em->flush();

        $this->addFlash('success', 'Follow request approved!');

        return $this->redirectToRoute('app_follow_requests');
    }

    #[Route('/follow-requests/{id}/reject', name: 'app_follow_reject', methods: ['POST'])]
    public function reject(
        FollowRequest $followRequest,
        EntityManagerInterface $em,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($followRequest->getTarget()?->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You can only reject your own requests.');
        }

        $em->remove($followRequest);
        $em->flush();

        $this->addFlash('success', 'Follow request rejected.');

        return $this->redirectToRoute('app_follow_requests');
    }

    #[Route('/followers', name: 'app_followers')]
    public function followers(FollowRequestRepository $followRequestRepo): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $followers = $followRequestRepo->findFollowers($currentUser);

        return $this->render('follow/followers.html.twig', [
            'followers' => $followers,
        ]);
    }

    #[Route('/following', name: 'app_following')]
    public function following(FollowRequestRepository $followRequestRepo): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $following = $followRequestRepo->findFollowing($currentUser);

        return $this->render('follow/following.html.twig', [
            'following' => $following,
        ]);
    }
}
