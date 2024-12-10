<?php

declare(strict_types=1);

namespace spec\Querify\Domain\User;

use PhpSpec\ObjectBehavior;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRole;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\UuidInterface;

class UserSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            Email::fromString('example@local.host'),
            'John Doe'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(User::class);
    }

    public function it_has_a_uuid(): void
    {
        $this->uuid->shouldNotBeNull();
        $this->uuid->shouldHaveType(UuidInterface::class);
    }

    public function it_has_created_and_updated_at_dates(): void
    {
        $this->createdAt->shouldBeAnInstanceOf(\DateTimeImmutable::class);
        $this->updatedAt->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }

    public function it_has_a_default_role(): void
    {
        $this->getRoles()->shouldContain(UserRole::ROLE_USER);
    }

    public function it_can_grant_privilege(): void
    {
        $role = UserRole::ROLE_USER;
        $this->grantPrivilege($role);
        $this->getRoles()->shouldContain($role);
    }

    public function it_does_not_grant_duplicate_privilege(): void
    {
        $role = UserRole::ROLE_USER;
        $this->grantPrivilege($role);
        $this->grantPrivilege($role);
        $this->getRoles()->shouldContain($role);
        $this->getRoles()->shouldHaveCount(1);
    }

    public function it_can_check_privilege(): void
    {
        $role = UserRole::ROLE_USER;
        $this->grantPrivilege($role);
        $this->hasPrivilege($role)->shouldReturn(true);
    }

    public function it_can_link_social_account(): void
    {
        $socialAccount = new UserSocialAccount(
            $this->getWrappedObject(),
            UserSocialAccountType::SLACK,
            'some-external-id',
            []
        );
        $this->linkSocialAccount($socialAccount);
        $this->getSocialAccounts()->shouldContain($socialAccount);
    }

    public function it_can_check_if_social_account_is_linked(): void
    {
        $socialAccount = new UserSocialAccount(
            $this->getWrappedObject(),
            UserSocialAccountType::SLACK,
            'some-external-id',
            []
        );
        $this->linkSocialAccount($socialAccount);
        $this->hasSocialAccountLinked(UserSocialAccountType::SLACK)->shouldReturn(true);
    }

    public function it_returns_null_if_social_account_is_not_linked(): void
    {
        $this->hasSocialAccountLinked(UserSocialAccountType::SLACK)->shouldReturn(false);
    }

    public function it_can_get_social_account_by_type(): void
    {
        $socialAccount = new UserSocialAccount(
            $this->getWrappedObject(),
            UserSocialAccountType::SLACK,
            'some-external-id',
            []
        );
        $this->linkSocialAccount($socialAccount);
        $this->getSocialAccount(UserSocialAccountType::SLACK)->shouldReturn($socialAccount);
    }

    public function it_should_return_null_when_social_account_not_found(): void
    {
        $this->getSocialAccount(UserSocialAccountType::SLACK)->shouldReturn(null);
    }

    public function it_can_compare_users(): void
    {
        $this->isEqualTo($this)->shouldReturn(true);
        $otherUser = new User(Email::fromString('example@local.host'), 'John Doe');
        $this->isEqualTo($otherUser)->shouldReturn(true);
    }

    public function it_should_return_false_when_comparing_with_different_user(): void
    {
        $otherUser = new User(
            Email::fromString('another@local.host'),
            'Maria'
        );
        $this->isEqualTo($otherUser)->shouldReturn(false);
    }
}
