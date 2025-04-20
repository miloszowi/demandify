<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Notification\Options;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

interface NotificationOptionsFactory
{
    public const string APPROVE_CALLBACK_KEY = 'approve';
    public const string DECLINE_CALLBACK_KEY = 'decline';

    public function supports(UserSocialAccountType $userSocialAccountType): bool;

    public function create(Demand $demand, NotificationType $notificationType, UserSocialAccount $userSocialAccount): MessageOptionsInterface;

    public function createForDecision(Notification $notification, User $approver, Status $status): MessageOptionsInterface;
}
