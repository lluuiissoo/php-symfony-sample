<?php

namespace App\Tests\Controller;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private function createUser(
        string $email,
        string $displayName,
        bool $wizardCompleted = true,
    ): User {
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
        $profile->setWizardCompleted($wizardCompleted);
        $profile->setUser($user);

        $em->persist($user);
        $em->persist($profile);
        $em->flush();

        return $user;
    }

    public function testDirectoryRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/users');
        $this->assertResponseRedirects('/login');
    }

    public function testDirectoryShowsUsers(): void
    {
        $client = static::createClient();
        $currentUser = $this->createUser('me@example.com', 'Me');
        $this->createUser('alice@example.com', 'Alice');
        $this->createUser('bob@example.com', 'Bob');

        $client->loginUser($currentUser);
        $client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Alice');
        $this->assertSelectorTextContains('body', 'Bob');
    }

    public function testDirectoryExcludesCurrentUser(): void
    {
        $client = static::createClient();
        $currentUser = $this->createUser('myself@example.com', 'Myself');
        $this->createUser('other@example.com', 'Other Person');

        $client->loginUser($currentUser);
        $crawler = $client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        // Current user should not appear in the list
        $this->assertStringNotContainsString('Myself', $crawler->filter('.card')->text());
    }

    public function testDirectorySearchFiltersResults(): void
    {
        $client = static::createClient();
        $currentUser = $this->createUser('searcher@example.com', 'Searcher');
        $this->createUser('alice2@example.com', 'Alice Wonder');
        $this->createUser('bob2@example.com', 'Bob Builder');

        $client->loginUser($currentUser);
        $client->request('GET', '/users?q=Alice');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Alice Wonder');
    }
}
