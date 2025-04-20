<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SaveSentNotification;

use Demandify\Application\Command\Command;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class SaveSentNotification implements Command
{
    public function __construct(
        public UuidInterface $demandUuid,
        public NotificationType $notificationType,
        public string $notificationIdentifier,
        public string $recipient,
        /** @var mixed[] */
        public array $options,
        public UserSocialAccountType $socialAccountType,
    ) {}

    public function toNotification(): Notification
    {
        return new Notification(
            $this->demandUuid,
            $this->notificationType,
            $this->recipient,
            $this->notificationIdentifier,
            $this->options,
            $this->socialAccountType,
        );
    }
}
