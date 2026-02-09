<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEmailGetterSetter(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testPasswordGetterSetter(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');
        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testRolesDefaultToRoleUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testRegisteredAtIsSetOnConstruction(): void
    {
        $user = new User();
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getRegisteredAt());
    }

    public function testIdIsNullByDefault(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
    }
}
