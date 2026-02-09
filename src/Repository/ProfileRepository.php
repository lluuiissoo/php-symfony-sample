<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Profile>
 */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function findPaginatableQueryExcludingUser(User $excludeUser, ?string $searchQuery = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->where('u.id != :excludeId')
            ->andWhere('p.wizardCompleted = true')
            ->setParameter('excludeId', $excludeUser->getId())
            ->orderBy('p.displayName', 'ASC');

        if ($searchQuery !== null && $searchQuery !== '') {
            $qb->andWhere('LOWER(p.displayName) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $searchQuery . '%');
        }

        return $qb;
    }
}
