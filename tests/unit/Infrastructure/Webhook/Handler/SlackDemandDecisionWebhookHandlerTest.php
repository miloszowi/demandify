<?php

declare(strict_types=1);

namespace Demandify\Tests\Infrastructure\Webhook\Handler;

use Demandify\Application\Command\ApproveDemand\ApproveDemand;
use Demandify\Application\Command\DeclineDemand\DeclineDemand;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountRepository;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\External\Slack\SlackConfiguration;
use Demandify\Infrastructure\Notification\Client\SlackNotificationClient;
use Demandify\Infrastructure\Webhook\Handler\SlackDemandDecisionWebhookHandler;
use Demandify\Infrastructure\Webhook\Request\SlackDemandDecisionWebhookRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
#[CoversClass(SlackDemandDecisionWebhookHandler::class)]
final class SlackDemandDecisionWebhookHandlerTest extends TestCase
{
    private SlackConfiguration $slackConfiguration;
    private MockObject|SerializerInterface $serializer;
    private MessageBusInterface|MockObject $messageBus;
    private MockObject|UserSocialAccountRepository $userSocialAccountRepository;
    private SlackDemandDecisionWebhookHandler $handler;

    protected function setUp(): void
    {
        $this->slackConfiguration = new SlackConfiguration(
            'client_id',
            'client_secret',
            'signing_secret',
            'oauth_bot_token',
        );
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->userSocialAccountRepository = $this->createMock(UserSocialAccountRepository::class);

        $this->handler = new SlackDemandDecisionWebhookHandler(
            $this->slackConfiguration,
            $this->serializer,
            $this->messageBus,
            $this->userSocialAccountRepository,
        );
    }

    public function testSupports(): void
    {
        $request = new Request([], ['payload' => 'test']);
        $request->headers->set('X-Slack-Request-Timestamp', '1234567890');
        $request->headers->set('X-Slack-Signature', 'test_signature');

        self::assertTrue($this->handler->supports($request, UserSocialAccountType::SLACK->value));
        self::assertFalse($this->handler->supports($request, UserSocialAccountType::GOOGLE->value));
    }

    public function testHandleSuccessfullyForApprovedDemand(): void
    {
        $request = new Request([], ['payload' => '{"callback_id": "550e8400-e29b-41d4-a716-446655440000", "user": {"id": "U123"}, "actions": [{"value": "approve"}]}']);
        $user = $this->createMock(User::class);
        $socialAccount = new UserSocialAccount($user, UserSocialAccountType::SLACK, 'U123');

        $slackRequest = new SlackDemandDecisionWebhookRequest('550e8400-e29b-41d4-a716-446655440000', 'U123', SlackNotificationClient::APPROVE_CALLBACK_KEY);

        $this->serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with('{"callback_id": "550e8400-e29b-41d4-a716-446655440000", "user": {"id": "U123"}, "actions": [{"value": "approve"}]}', SlackDemandDecisionWebhookRequest::class, 'json')
            ->willReturn($slackRequest)
        ;

        $this->userSocialAccountRepository
            ->expects(self::once())
            ->method('getByExternalIdAndType')
            ->with('U123', UserSocialAccountType::SLACK)
            ->willReturn($socialAccount)
        ;

        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(ApproveDemand::class))
            ->willReturn($mockEnvelope)
        ;

        $this->handler->handle($request);
    }

    public function testHandleSuccessfullyForDeclinedDemand(): void
    {
        $request = new Request([], ['payload' => '{"callback_id": "550e8400-e29b-41d4-a716-446655440000", "user": {"id": "U123"}, "actions": [{"value": "decline"}]}']);
        $user = $this->createMock(User::class);
        $socialAccount = new UserSocialAccount($user, UserSocialAccountType::SLACK, 'U123');

        $slackRequest = new SlackDemandDecisionWebhookRequest('550e8400-e29b-41d4-a716-446655440000', 'U123', SlackNotificationClient::DECLINE_CALLBACK_KEY);

        $this->serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with('{"callback_id": "550e8400-e29b-41d4-a716-446655440000", "user": {"id": "U123"}, "actions": [{"value": "decline"}]}', SlackDemandDecisionWebhookRequest::class, 'json')
            ->willReturn($slackRequest)
        ;

        $this->userSocialAccountRepository
            ->expects(self::once())
            ->method('getByExternalIdAndType')
            ->with('U123', UserSocialAccountType::SLACK)
            ->willReturn($socialAccount)
        ;

        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DeclineDemand::class))
            ->willReturn($mockEnvelope)
        ;

        $this->handler->handle($request);
    }

    public function testIsValid(): void
    {
        $request = new Request([], [], [], [], [], [], 'test_content');
        $request->headers->set('X-Slack-Request-Timestamp', '1234567890');
        $request->headers->set('X-Slack-Signature', 'v0='.hash_hmac('sha256', 'v0:1234567890:test_content', 'signing_secret'));

        self::assertTrue($this->handler->isValid($request));
    }
}
