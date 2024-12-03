<?php

declare(strict_types=1);

namespace spec\Querify\Domain\User;

use PhpSpec\ObjectBehavior;
use Querify\Domain\User\Email;

class EmailSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedThrough(
            'fromString',
            ['example@local.host']
        );
        $this->shouldHaveType(Email::class);
    }

    public function it_throws_exception_if_email_is_empty(): void
    {
        $this->beConstructedThrough('fromString', ['']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_email_is_invalid(): void
    {
        $this->beConstructedThrough('fromString', ['invalid@email']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_return_string(): void
    {
        $this->beConstructedThrough('fromString', ['example@local.host']);

        $this->__toString()->shouldReturn('example@local.host');
    }

    public function it_should_return_true_if_other_email_is_the_same(): void
    {
        $this->beConstructedThrough('fromString', ['example@local.host']);

        $this->isEqualTo(Email::fromString('example@local.host'))->shouldReturn(true);
    }

    public function it_should_return_true_if_other_email_is_not_the_same(): void
    {
        $this->beConstructedThrough('fromString', ['example@local.host']);

        $this->isEqualTo(Email::fromString('different.email@local.host'))->shouldReturn(false);
    }
}
