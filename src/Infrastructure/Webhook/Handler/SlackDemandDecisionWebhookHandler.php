<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Webhook\Handler;

use Demandify\Application\Command\ApproveDemand\ApproveDemand;
use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\DeclineDemand\DeclineDemand;
use Demandify\Domain\UserSocialAccount\UserSocialAccountRepository;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\External\Slack\SlackConfiguration;
use Demandify\Infrastructure\Webhook\Request\SlackDemandDecisionWebhookRequest;
use Demandify\Infrastructure\Webhook\WebhookHandler;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class SlackDemandDecisionWebhookHandler implements WebhookHandler
{
    public function __construct(
        private readonly SlackConfiguration $slackConfiguration,
        private readonly SerializerInterface $serializer,
        private readonly CommandBus $commandBus,
        private readonly UserSocialAccountRepository $userSocialAccountRepository,
    ) {}

    public function supports(Request $request, string $type): bool
    {
        return strtolower($type) === UserSocialAccountType::SLACK->value
            && null !== $request->get('payload')
            && null !== $request->headers->get('X-Slack-Request-Timestamp')
            && null !== $request->headers->get('X-Slack-Signature');
    }

    public function handle(Request $request): void
    {
        $slackRequest = $this->serializer->deserialize(
            $request->get('payload'),
            SlackDemandDecisionWebhookRequest::class,
            JsonEncoder::FORMAT
        );

        $socialAccount = $this->userSocialAccountRepository->getByExternalIdAndType(
            $slackRequest->slackUserId,
            UserSocialAccountType::SLACK
        );

        $command = match ($slackRequest->isApproved()) {
            true => new ApproveDemand(
                Uuid::fromString($slackRequest->demandUuid),
                $socialAccount->user->uuid
            ),
            false => new DeclineDemand(
                Uuid::fromString($slackRequest->demandUuid),
                $socialAccount->user->uuid
            ),
        };
        $this->commandBus->dispatch($command);
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
