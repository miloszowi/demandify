<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\SendDemandNotification;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotificationHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Notification\NotificationService;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(SendDemandNotificationHandler::class)]
final class SendDemandNotificationHandlerTest extends TestCase
{
    private MockObject|UserRepository $userRepositoryMock;
    private DemandRepository|MockObject $demandRepositoryMock;
    private MockObject|NotificationService $notificationServiceMock;
    private SendDemandNotificationHandler $sendDemandNotificationHandler;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->notificationServiceMock = $this->createMock(NotificationService::class);
        $this->sendDemandNotificationHandler = new SendDemandNotificationHandler(
            $this->userRepositoryMock,
            $this->demandRepositoryMock,
            $this->notificationServiceMock,
        );
    }

    public function testSendsNotifications(): void
    {
        $recipientMock = $this->createMock(User::class);
        $demandUuid = Uuid::uuid4();
        $demandMock = $this->createMock(Demand::class);
        $notificationCommand = new SendDemandNotification(
            Uuid::uuid4(),
            $demandUuid,
            NotificationType::NEW_DEMAND
        );
        $userSocialAccount = new UserSocialAccount(
            $recipientMock,
            UserSocialAccountType::SLACK,
            'externalId',
        );
        $userSocialAccount->setNotifiable(true);

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($notificationCommand->recipientUuid)
            ->willReturn($recipientMock)
        ;

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demandUuid)
            ->willReturn($demandMock)
        ;

        $recipientMock
            ->expects(self::once())
            ->method('getSocialAccounts')
            ->willReturn(
                new ArrayCollection(
                    [$userSocialAccount],
                )
            )
        ;

        $this->notificationServiceMock
            ->expects(self::once())
            ->method('send')
            ->with($notificationCommand->notificationType, $demandMock, self::isInstanceOf(UserSocialAccount::class))
        ;

        $this->sendDemandNotificationHandler->__invoke($notificationCommand);
    }

    public function testDoesNotSendForNotNotifiableSocialAccounts(): void
    {
        $recipientMock = $this->createMock(User::class);
        $notificationCommand = new SendDemandNotification(
            Uuid::uuid4(),
            Uuid::uuid4(),
            NotificationType::NEW_DEMAND
        );
        $userSocialAccount = new UserSocialAccount(
            $recipientMock,
            UserSocialAccountType::SLACK,
            'externalId',
        );
        $userSocialAccount->setNotifiable(false);

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
                    [$userSocialAccount],
                )
            )
        ;

        $this->notificationServiceMock
            ->expects(self::never())
            ->method('send')
            ->withAnyParameters()
        ;

        $this->sendDemandNotificationHandler->__invoke($notificationCommand);
    }

    public function testHandlesNoSocialAccounts(): void
    {
        $recipientMock = $this->createMock(User::class);
        $notificationCommand = new SendDemandNotification(
            Uuid::uuid4(),
            Uuid::uuid4(),
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
            ->withAnyParameters()
        ;

        $this->sendDemandNotificationHandler->__invoke($notificationCommand);
    }
}
