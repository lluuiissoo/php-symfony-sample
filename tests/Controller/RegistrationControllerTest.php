<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testRegisterWithValidData(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Register', [
            'registration[email]' => 'newuser@example.com',
            'registration[plainPassword]' => 'password123',
        ]);

        $this->assertResponseRedirects('/profile/wizard');

        /** @var UserRepository $userRepo */
        $userRepo = static::getContainer()->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => 'newuser@example.com']);
        $this->assertNotNull($user);
    }

    public function testRegisterWithDuplicateEmail(): void
    {
        $client = static::createClient();

        // Create first user
        $client->request('GET', '/register');
        $client->submitForm('Register', [
            'registration[email]' => 'duplicate@example.com',
            'registration[plainPassword]' => 'password123',
        ]);

        // Try to register with same email
        $client->request('GET', '/register');
        $client->submitForm('Register', [
            'registration[email]' => 'duplicate@example.com',
            'registration[plainPassword]' => 'password123',
        ]);

        $this->assertResponseIsUnprocessable();
        $this->assertSelectorExists('.form-error');
    }

    public function testRegisterWithShortPassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Register', [
            'registration[email]' => 'short@example.com',
            'registration[plainPassword]' => 'short',
        ]);

        $this->assertResponseIsUnprocessable();
    }
}
