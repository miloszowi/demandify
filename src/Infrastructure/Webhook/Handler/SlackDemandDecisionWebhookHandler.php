<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Webhook\Handler;

use Querify\Application\Command\ApproveDemand\ApproveDemand;
use Querify\Application\Command\DeclineDemand\DeclineDemand;
use Querify\Domain\Exception\DomainLogicException;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\External\Slack\SlackConfiguration;
use Querify\Infrastructure\Webhook\Request\SlackWebhookRequest;
use Querify\Infrastructure\Webhook\WebhookHandler;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class SlackDemandDecisionWebhookHandler implements WebhookHandler
{
    public function __construct(
        private readonly SlackConfiguration $slackConfiguration,
        private readonly SerializerInterface $serializer,
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function supports(Request $request, string $type): bool
    {
        return strtoupper($type) === UserSocialAccountType::SLACK->value
            && null !== $request->get('payload')
            && null !== $request->headers->get('X-Slack-Request-Timestamp')
            && null !== $request->headers->get('X-Slack-Signature');
    }

    public function handle(Request $request): void
    {
        $slackRequest = $this->serializer->deserialize(
            $request->get('payload'),
            SlackWebhookRequest::class,
            JsonEncoder::FORMAT
        );

        $command = match ($slackRequest->decision) {
            'approve' => new ApproveDemand(
                Uuid::fromString($slackRequest->demandUuid),
                $slackRequest->slackUserId,
                UserSocialAccountType::SLACK
            ),
            'decline' => new DeclineDemand(
                Uuid::fromString($slackRequest->demandUuid),
                $slackRequest->slackUserId,
                UserSocialAccountType::SLACK
            ),
            default => throw new DomainLogicException('No suitable action for this decision') // todo
        };
        $this->messageBus->dispatch($command);
    }

    public function isValid(Request $request): bool
    {
        $sigBaseString = \sprintf(
            'v0:%s:%s',
            $request->headers->get('X-Slack-Request-Timestamp'),
            $request->getContent(),
        );

        $appSignature = \sprintf(
            '%s=%s',
            'v0',
            hash_hmac(
                'sha256',
                $sigBaseString,
                $this->slackConfiguration->signingSecret,
            )
        );

        return $appSignature === $request->headers->get('X-Slack-Signature');
    }
}
