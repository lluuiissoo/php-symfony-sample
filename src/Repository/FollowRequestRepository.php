<?php

namespace App\Repository;

use App\Entity\FollowRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FollowRequest>
 */
class FollowRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FollowRequest::class);
    }

    /** @return FollowRequest[] */
    public function findPendingForTarget(User $target): array
    {
        return $this->createQueryBuilder('fr')
            ->where('fr.target = :target')
            ->andWhere('fr.status = :status')
            ->setParameter('target', $target)
            ->setParameter('status', FollowRequest::STATUS_PENDING)
            ->orderBy('fr.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return FollowRequest[] */
    public function findFollowers(User $user): array
    {
        return $this->createQueryBuilder('fr')
            ->where('fr.target = :user')
            ->andWhere('fr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', FollowRequest::STATUS_APPROVED)
            ->orderBy('fr.resolvedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return FollowRequest[] */
    public function findFollowing(User $user): array
    {
        return $this->createQueryBuilder('fr')
            ->where('fr.requester = :user')
            ->andWhere('fr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', FollowRequest::STATUS_APPROVED)
            ->orderBy('fr.resolvedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingBetween(User $requester, User $target): ?FollowRequest
    {
        return $this->createQueryBuilder('fr')
            ->where('fr.requester = :requester')
            ->andWhere('fr.target = :target')
            ->andWhere('fr.status = :status')
            ->setParameter('requester', $requester)
            ->setParameter('target', $target)
            ->setParameter('status', FollowRequest::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findApprovedBetween(User $requester, User $target): ?FollowRequest
    {
        return $this->createQueryBuilder('fr')
            ->where('fr.requester = :requester')
            ->andWhere('fr.target = :target')
            ->andWhere('fr.status = :status')
            ->setParameter('requester', $requester)
            ->setParameter('target', $target)
            ->setParameter('status', FollowRequest::STATUS_APPROVED)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countPendingForTarget(User $target): int
    {
        return (int) $this->createQueryBuilder('fr')
            ->select('COUNT(fr.id)')
            ->where('fr.target = :target')
            ->andWhere('fr.status = :status')
            ->setParameter('target', $target)
            ->setParameter('status', FollowRequest::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRelationshipStatus(User $from, User $to): ?string
    {
        $fr = $this->createQueryBuilder('fr')
            ->where('fr.requester = :from')
            ->andWhere('fr.target = :to')
            ->andWhere('fr.status IN (:statuses)')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('statuses', [FollowRequest::STATUS_PENDING, FollowRequest::STATUS_APPROVED])
            ->getQuery()
            ->getOneOrNullResult();

        return $fr?->getStatus();
    }
}
