<?php

declare(strict_types=1);

namespace Querify\Tests\Infrastructure\Webhook\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\ApproveDemand\ApproveDemand;
use Querify\Application\Command\DeclineDemand\DeclineDemand;
use Querify\Domain\User\User;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountRepository;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\External\Slack\SlackConfiguration;
use Querify\Infrastructure\Notification\Client\SlackNotificationClient;
use Querify\Infrastructure\Webhook\Handler\SlackDemandDecisionWebhookHandler;
use Querify\Infrastructure\Webhook\Request\SlackDemandDecisionWebhookRequest;
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
            'test_app_id',
            'test_client_id',
            'test_client_secret',
            'test_signing_secret',
            'test_oauth_bot_token',
            'test_oauth_redirect_uri',
            'test_oauth_state_hash_key'
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
        $request->headers->set('X-Slack-Signature', 'v0='.hash_hmac('sha256', 'v0:1234567890:test_content', 'test_signing_secret'));

        self::assertTrue($this->handler->isValid($request));
    }
}
