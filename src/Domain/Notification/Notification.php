<?php

declare(strict_types=1);

namespace Querify\Domain\Notification;

use Doctrine\ORM\Mapping as ORM;
use Querify\Domain\Demand\Demand;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;

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
            ORM\ManyToOne(targetEntity: Demand::class, inversedBy: 'notifications'),
            ORM\JoinColumn(name: 'demand_uuid', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE'),
        ]
        public readonly Demand $demand,
        #[ORM\Column(type: 'string', nullable: false)]
        public readonly string $action,
        #[ORM\Column(type: 'string', nullable: false)]
        public readonly string $notificationIdentifier,
        #[ORM\Column(type: 'string', nullable: false)]
        public readonly string $channel,
        #[ORM\Column(nullable: false)]
        public readonly UserSocialAccountType $type
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }
}
