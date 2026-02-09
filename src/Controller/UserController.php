<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProfileRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'app_users')]
    public function directory(
        Request $request,
        ProfileRepository $profileRepo,
        PaginatorInterface $paginator,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $searchQuery = $request->query->get('q');

        $queryBuilder = $profileRepo->findPaginatableQueryExcludingUser($currentUser, $searchQuery);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20,
        );

        return $this->render('user/directory.html.twig', [
            'pagination' => $pagination,
            'searchQuery' => $searchQuery,
        ]);
    }
}
