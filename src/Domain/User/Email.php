<?php

declare(strict_types=1);

namespace Demandify\Domain\User;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
class Email
{
    private function __construct(
        #[ORM\Column(name: 'email', type: 'string', unique: true)]
        private readonly string $email,
    ) {
        Assert::email($this->email);
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function isEqualTo(self $email): bool
    {
        return $email->email === $this->email;
    }
}
