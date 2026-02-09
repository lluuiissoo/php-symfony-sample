<?php

namespace App\Tests\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Repository\FollowRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FollowControllerTest extends WebTestCase
{
    private function createUser(string $email, string $displayName): User
    {
        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($hasher->hashPassword($user, 'password123'));

        $profile = new Profile();
        $profile->setDisplayName($displayName);
        $profile->setWizardCompleted(true);
        $profile->setUser($user);

        $em->persist($user);
        $em->persist($profile);
        $em->flush();

        return $user;
    }

    public function testSendFollowRequest(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('alice@example.com', 'Alice');
        $bob = $this->createUser('bob@example.com', 'Bob');

        $client->loginUser($alice);
        $client->request('POST', '/follow/' . $bob->getId());

        $this->assertResponseRedirects('/profile/' . $bob->getId());
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Follow request sent');
    }

    public function testCannotFollowSelf(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('self@example.com', 'Self');

        $client->loginUser($alice);
        $client->request('POST', '/follow/' . $alice->getId());

        $this->assertResponseRedirects('/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-error', 'cannot follow yourself');
    }

    public function testDuplicatePendingRequest(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('alice2@example.com', 'Alice2');
        $bob = $this->createUser('bob2@example.com', 'Bob2');

        $client->loginUser($alice);
        $client->request('POST', '/follow/' . $bob->getId());
        $client->request('POST', '/follow/' . $bob->getId());

        $this->assertResponseRedirects('/profile/' . $bob->getId());
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-info', 'already pending');
    }

    public function testPendingRequestsList(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('alice3@example.com', 'Alice3');
        $bob = $this->createUser('bob3@example.com', 'Bob3');

        $client->loginUser($alice);
        $client->request('POST', '/follow/' . $bob->getId());

        $client->loginUser($bob);
        $client->request('GET', '/follow-requests');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Alice3');
    }

    public function testApproveFollowRequest(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('alice4@example.com', 'Alice4');
        $bob = $this->createUser('bob4@example.com', 'Bob4');

        $client->loginUser($alice);
        $client->request('POST', '/follow/' . $bob->getId());

        /** @var FollowRequestRepository $frRepo */
        $frRepo = static::getContainer()->get(FollowRequestRepository::class);
        $fr = $frRepo->findPendingBetween($alice, $bob);

        $client->loginUser($bob);
        $client->request('POST', '/follow-requests/' . $fr->getId() . '/approve');

        $this->assertResponseRedirects('/follow-requests');

        // Verify it's approved
        $client->request('GET', '/followers');
        $this->assertSelectorTextContains('body', 'Alice4');
    }

    public function testRejectFollowRequest(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('alice5@example.com', 'Alice5');
        $bob = $this->createUser('bob5@example.com', 'Bob5');

        $client->loginUser($alice);
        $client->request('POST', '/follow/' . $bob->getId());

        /** @var FollowRequestRepository $frRepo */
        $frRepo = static::getContainer()->get(FollowRequestRepository::class);
        $fr = $frRepo->findPendingBetween($alice, $bob);

        $client->loginUser($bob);
        $client->request('POST', '/follow-requests/' . $fr->getId() . '/reject');

        $this->assertResponseRedirects('/follow-requests');
    }

    public function testUnfollow(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('alice6@example.com', 'Alice6');
        $bob = $this->createUser('bob6@example.com', 'Bob6');

        // Alice follows Bob
        $client->loginUser($alice);
        $client->request('POST', '/follow/' . $bob->getId());

        /** @var FollowRequestRepository $frRepo */
        $frRepo = static::getContainer()->get(FollowRequestRepository::class);
        $fr = $frRepo->findPendingBetween($alice, $bob);

        // Bob approves
        $client->loginUser($bob);
        $client->request('POST', '/follow-requests/' . $fr->getId() . '/approve');

        // Alice unfollows
        $client->loginUser($alice);
        $client->request('POST', '/unfollow/' . $bob->getId());

        $this->assertResponseRedirects('/profile/' . $bob->getId());
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Unfollowed');
    }

    public function testFollowersPage(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('alice7@example.com', 'Alice7');

        $client->loginUser($alice);
        $client->request('GET', '/followers');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'No followers yet');
    }

    public function testFollowingPage(): void
    {
        $client = static::createClient();
        $alice = $this->createUser('alice8@example.com', 'Alice8');

        $client->loginUser($alice);
        $client->request('GET', '/following');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'not following anyone');
    }
}
