<?php

namespace App\Tests\Entity;

use App\Entity\Profile;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    public function testDisplayNameGetterSetter(): void
    {
        $profile = new Profile();
        $profile->setDisplayName('John Doe');
        $this->assertSame('John Doe', $profile->getDisplayName());
    }

    public function testBioGetterSetter(): void
    {
        $profile = new Profile();
        $this->assertNull($profile->getBio());
        $profile->setBio('A short bio.');
        $this->assertSame('A short bio.', $profile->getBio());
    }

    public function testPhotoFilenameGetterSetter(): void
    {
        $profile = new Profile();
        $this->assertNull($profile->getPhotoFilename());
        $profile->setPhotoFilename('photo.jpg');
        $this->assertSame('photo.jpg', $profile->getPhotoFilename());
    }

    public function testWizardCompletedDefaultFalse(): void
    {
        $profile = new Profile();
        $this->assertFalse($profile->isWizardCompleted());
    }

    public function testWizardCompletedGetterSetter(): void
    {
        $profile = new Profile();
        $profile->setWizardCompleted(true);
        $this->assertTrue($profile->isWizardCompleted());
    }

    public function testUserRelationship(): void
    {
        $profile = new Profile();
        $user = new User();
        $user->setEmail('test@example.com');
        $profile->setUser($user);
        $this->assertSame($user, $profile->getUser());
    }
}
