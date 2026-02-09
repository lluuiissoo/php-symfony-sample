<?php

namespace App\Tests\Controller;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileControllerTest extends WebTestCase
{
    private function createAndLoginUser(
        KernelBrowser $client,
        string $email = 'test@example.com',
        bool $wizardCompleted = false,
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
        $profile->setDisplayName('Test User');
        $profile->setWizardCompleted($wizardCompleted);
        $profile->setUser($user);

        $em->persist($user);
        $em->persist($profile);
        $em->flush();

        $client->loginUser($user);

        return $user;
    }

    public function testWizardRedirectsForNewUser(): void
    {
        $client = static::createClient();
        $this->createAndLoginUser($client, 'new@example.com', false);

        $client->request('GET', '/users');
        $this->assertResponseRedirects('/profile/wizard');
    }

    public function testWizardPageLoads(): void
    {
        $client = static::createClient();
        $this->createAndLoginUser($client, 'wizard@example.com', false);

        $client->request('GET', '/profile/wizard');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testWizardSavesProfileData(): void
    {
        $client = static::createClient();
        $this->createAndLoginUser($client, 'wizard2@example.com', false);

        $client->request('GET', '/profile/wizard');
        $client->submitForm('Complete Profile', [
            'profile_wizard[displayName]' => 'Jane Doe',
            'profile_wizard[bio]' => 'I love coding.',
        ]);

        $this->assertResponseRedirects('/users');
    }

    public function testEditPageLoads(): void
    {
        $client = static::createClient();
        $this->createAndLoginUser($client, 'edit@example.com', true);

        $client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testEditSavesChanges(): void
    {
        $client = static::createClient();
        $this->createAndLoginUser($client, 'edit2@example.com', true);

        $client->request('GET', '/profile/edit');
        $client->submitForm('Save Changes', [
            'profile_edit[displayName]' => 'Updated Name',
            'profile_edit[bio]' => 'Updated bio.',
        ]);

        $this->assertResponseRedirects('/profile/edit');
    }

    public function testShowProfilePage(): void
    {
        $client = static::createClient();
        $user = $this->createAndLoginUser($client, 'viewer@example.com', true);

        $client->request('GET', '/profile/' . $user->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Test User');
    }
}
