<?php

namespace App\Tests\Controller;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private function createTestUser(string $email = 'test@example.com', string $password = 'password123'): void
    {
        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($hasher->hashPassword($user, $password));

        $profile = new Profile();
        $profile->setDisplayName('Test User');
        $profile->setWizardCompleted(true);
        $profile->setUser($user);

        $em->persist($user);
        $em->persist($profile);
        $em->flush();
    }

    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $this->createTestUser();

        $client->request('GET', '/login');
        $client->submitForm('Login', [
            '_username' => 'test@example.com',
            '_password' => 'password123',
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $this->createTestUser();

        $client->request('GET', '/login');
        $client->submitForm('Login', [
            '_username' => 'test@example.com',
            '_password' => 'wrongpassword',
        ]);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.flash-error');
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $this->createTestUser();

        // Login first
        $client->request('GET', '/login');
        $client->submitForm('Login', [
            '_username' => 'test@example.com',
            '_password' => 'password123',
        ]);
        $client->followRedirect();

        // Now logout
        $client->request('GET', '/logout');
        $this->assertResponseRedirects();
    }
}
