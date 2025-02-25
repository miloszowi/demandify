<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Console;

use Demandify\Application\Command\RegisterUser\RegisterUser;
use Demandify\Infrastructure\Console\CreateUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(CreateUser::class)]
final class CreateUserTest extends TestCase
{
    private MessageBusInterface|MockObject $messageBusMock;
    private CreateUser $createUser;

    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->createUser = new CreateUser($this->messageBusMock);
    }

    public function testConfigureCommand(): void
    {
        $this->createUser->configure();
        self::assertSame('user:create', $this->createUser->getName());
        self::assertSame('Creates a new user.', $this->createUser->getDescription());
        self::assertSame('This command allows you to create a user/superuser.', $this->createUser->getHelp());
    }

    public function testExecuteUserCreation(): void
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);
        $helperMock = $this->createMock(QuestionHelper::class);
        $helperSetMock = $this->createMock(HelperSet::class);

        $helperMock
            ->expects(self::exactly(3))
            ->method('ask')
            ->with($inputMock, $outputMock, self::isType('object'))
            ->willReturnOnConsecutiveCalls('example@local.host', 'Test User', 'ROLE_USER')
        ;
        $helperSetMock
            ->expects(self::exactly(3))
            ->method('get')
            ->with('question')
            ->willReturnOnConsecutiveCalls(
                $helperMock,
                $helperMock,
                $helperMock
            )
        ;
        $this->createUser->setHelperSet($helperSetMock);

        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBusMock->expects(self::once())->method('dispatch')->with(self::isInstanceOf(RegisterUser::class))
            ->willReturn($mockEnvelope)
        ;
        $result = $this->createUser->execute($inputMock, $outputMock);
        self::assertSame(0, $result);
    }
}
