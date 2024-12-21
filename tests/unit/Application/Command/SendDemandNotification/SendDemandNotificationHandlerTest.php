<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Application\Command\SendDemandNotification;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Application\Command\SendDemandNotification\SendDemandNotificationHandler;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationService;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(SendDemandNotificationHandler::class)]
final class SendDemandNotificationHandlerTest extends TestCase
{
    private MockObject|UserRepository $userRepositoryMock;
    private MockObject|NotificationService $notificationServiceMock;
    private MockObject|NotificationRepository $notificationRepositoryMock;
    private SendDemandNotificationHandler $sendDemandNotificationHandler;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->notificationServiceMock = $this->createMock(NotificationService::class);
        $this->notificationRepositoryMock = $this->createMock(NotificationRepository::class);
        $this->sendDemandNotificationHandler = new SendDemandNotificationHandler($this->userRepositoryMock, $this->notificationServiceMock, $this->notificationRepositoryMock);
    }

    public function testSendsNotificationsAndSaves(): void
    {
        $recipientMock = $this->createMock(User::class);
        $notificationMock = $this->createMock(Notification::class);
        $demandMock = $this->createMock(Demand::class);
        $notificationCommand = new SendDemandNotification(
            Uuid::uuid4(),
            $demandMock,
            NotificationType::NEW_DEMAND
        );

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($notificationCommand->recipientUuid)
            ->willReturn($recipientMock)
        ;
        $recipientMock
            ->expects(self::once())
            ->method('getSocialAccounts')
            ->willReturn(
                new ArrayCollection(
                    [
                        new UserSocialAccount(
                            $recipientMock,
                            UserSocialAccountType::SLACK,
                            'externalId',
                        ),
                    ],
                )
            )
        ;

        $this->notificationServiceMock
            ->expects(self::once())
            ->method('send')
            ->with($notificationCommand->notificationType, $notificationCommand->demand, self::isInstanceOf(UserSocialAccount::class))
            ->willReturn($notificationMock)
        ;
        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with($notificationMock)
        ;

        $this->sendDemandNotificationHandler->__invoke($notificationCommand);
    }

    public function testHandlesNoSocialAccounts(): void
    {
        $recipientMock = $this->createMock(User::class);
        $notificationMock = $this->createMock(Notification::class);
        $demandMock = $this->createMock(Demand::class);
        $notificationCommand = new SendDemandNotification(
            Uuid::uuid4(),
            $demandMock,
            NotificationType::NEW_DEMAND
        );

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($notificationCommand->recipientUuid)
            ->willReturn($recipientMock)
        ;
        $recipientMock
            ->expects(self::once())
            ->method('getSocialAccounts')
            ->willReturn(new ArrayCollection([]))
        ;
        $this->notificationServiceMock
            ->expects(self::never())
            ->method('send')
            ->with($notificationCommand->notificationType, $notificationCommand->demand, self::isInstanceOf(UserSocialAccount::class))
            ->willReturn($notificationMock)
        ;
        $this->notificationRepositoryMock
            ->expects(self::never())
            ->method('save')
            ->with($notificationMock)
        ;

        $this->sendDemandNotificationHandler->__invoke($notificationCommand);
    }
}
