<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Notification;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Infrastructure\Notification\NotificationOptionsFactory;
use Demandify\Infrastructure\Notification\NotificationService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(NotificationService::class)]
final class NotificationServiceTest extends TestCase
{
    private MockObject|NotificationOptionsFactory $optionsFactory;
    private ChatterInterface|MockObject $chatter;
    private LoggerInterface|MockObject $logger;
    private NotificationService $service;

    protected function setUp(): void
    {
        $this->optionsFactory = $this->createMock(NotificationOptionsFactory::class);
        $this->chatter = $this->createMock(ChatterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new NotificationService(
            $this->optionsFactory,
            $this->chatter,
            $this->logger
        );
    }

    public function testSend(): void
    {
        $demand = new Demand(
            $this->createMock(User::class),
            'some_service',
            'some_content',
            'some_reason',
        );

        $notificationType = NotificationType::NEW_DEMAND;
        $userAccount = self::createStub(UserSocialAccount::class);
        $options = $this->createMock(MessageOptionsInterface::class);

        $this->optionsFactory
            ->expects(self::once())
            ->method('create')
            ->with($demand, $notificationType, $userAccount)
            ->willReturn($options)
        ;

        $this->chatter
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (ChatMessage $message) use ($demand, $options) {
                return str_contains($message->getSubject(), $demand->uuid->toString())
                    && $message->getOptions() === $options;
            }))
        ;

        $this->service->send($notificationType, $demand, $userAccount);
    }

    public function testLogsErrorOnException(): void
    {
        $demand = new Demand(
            $this->createMock(User::class),
            'some_service',
            'some_content',
            'some_reason',
        );

        $notificationType = NotificationType::NEW_DEMAND;
        $userAccount = self::createStub(UserSocialAccount::class);
        $options = $this->createMock(MessageOptionsInterface::class);

        $this->optionsFactory
            ->expects(self::once())
            ->method('create')
            ->with($demand, $notificationType, $userAccount)
            ->willReturn($options)
        ;

        $this->chatter
            ->expects(self::once())
            ->method('send')
            ->willThrowException(
                new TransportException(
                    'some error',
                    $this->createMock(ResponseInterface::class)
                )
            )
        ;

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Failed to send notification',
                self::arrayHasKey('exception_message')
            )
        ;

        $this->service->send($notificationType, $demand, $userAccount);
    }

    public function testUpdateWithDecisionLogsErrorOnException(): void
    {
        $demand = new Demand(
            $this->createMock(User::class),
            'some_service',
            'some_content',
            'some_reason',
        );
        $demand->approveBy($this->createMock(User::class));

        $options = $this->createMock(MessageOptionsInterface::class);
        $notificationMock = $this->createMock(Notification::class);
        $this->optionsFactory
            ->expects(self::once())
            ->method('createForDecision')
            ->with($notificationMock, $demand->approver, $demand->status)
            ->willReturn($options)
        ;

        $this->chatter
            ->expects(self::once())
            ->method('send')
            ->willThrowException(
                new TransportException(
                    'some error',
                    $this->createMock(ResponseInterface::class)
                )
            )
        ;

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Failed to send updated notification',
                self::arrayHasKey('exception_message')
            )
        ;

        $this->service->updateWithDecision($notificationMock, $demand);
    }
}
