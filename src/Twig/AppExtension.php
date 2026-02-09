<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\FollowRequestRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private Security $security,
        private FollowRequestRepository $followRequestRepo,
    ) {
    }

    /** @return array<string, mixed> */
    public function getGlobals(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return ['pending_follow_count' => 0];
        }

        return [
            'pending_follow_count' => $this->followRequestRepo->countPendingForTarget($user),
        ];
    }
}
