<?php

declare(strict_types=1);

namespace Demandify\Domain\Notification;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[
    ORM\Entity(repositoryClass: NotificationRepository::class),
    ORM\Table('`notifications`')
]
class Notification
{
    #[ORM\Column(type: 'datetimetz_immutable')]
    public readonly \DateTimeImmutable $createdAt;

    public function __construct(
        #[
            ORM\Id,
            ORM\Column(type: 'uuid', nullable: false)
        ]
        public readonly UuidInterface $demandUuid,
        #[
            ORM\Id,
            ORM\Column(nullable: false)
        ]
        public readonly NotificationType $type,
        #[ORM\Column(type: 'string', nullable: false)]
        public readonly string $recipient,
        #[ORM\Column(type: 'string', nullable: false)]
        public readonly string $notificationIdentifier,
        /** @var mixed[] $options */
        #[ORM\Column(type: 'json')]
        public readonly array $options,
        #[ORM\Column(nullable: false)]
        public readonly UserSocialAccountType $socialAccountType
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }
}
