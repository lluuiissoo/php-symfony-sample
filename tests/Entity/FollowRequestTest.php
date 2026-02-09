<?php

namespace App\Tests\Entity;

use App\Entity\FollowRequest;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class FollowRequestTest extends TestCase
{
    public function testRequesterGetterSetter(): void
    {
        $fr = new FollowRequest();
        $user = new User();
        $user->setEmail('requester@example.com');
        $fr->setRequester($user);
        $this->assertSame($user, $fr->getRequester());
    }

    public function testTargetGetterSetter(): void
    {
        $fr = new FollowRequest();
        $user = new User();
        $user->setEmail('target@example.com');
        $fr->setTarget($user);
        $this->assertSame($user, $fr->getTarget());
    }

    public function testDefaultStatusIsPending(): void
    {
        $fr = new FollowRequest();
        $this->assertSame(FollowRequest::STATUS_PENDING, $fr->getStatus());
        $this->assertTrue($fr->isPending());
        $this->assertFalse($fr->isApproved());
    }

    public function testStatusTransitionToApproved(): void
    {
        $fr = new FollowRequest();
        $fr->setStatus(FollowRequest::STATUS_APPROVED);
        $this->assertTrue($fr->isApproved());
        $this->assertFalse($fr->isPending());
    }

    public function testStatusTransitionToRejected(): void
    {
        $fr = new FollowRequest();
        $fr->setStatus(FollowRequest::STATUS_REJECTED);
        $this->assertSame(FollowRequest::STATUS_REJECTED, $fr->getStatus());
        $this->assertFalse($fr->isPending());
        $this->assertFalse($fr->isApproved());
    }

    public function testRequestedAtIsSetOnConstruction(): void
    {
        $fr = new FollowRequest();
        $this->assertInstanceOf(\DateTimeImmutable::class, $fr->getRequestedAt());
    }

    public function testResolvedAtIsNullableByDefault(): void
    {
        $fr = new FollowRequest();
        $this->assertNull($fr->getResolvedAt());
    }

    public function testResolvedAtGetterSetter(): void
    {
        $fr = new FollowRequest();
        $now = new \DateTimeImmutable();
        $fr->setResolvedAt($now);
        $this->assertSame($now, $fr->getResolvedAt());
    }
}
