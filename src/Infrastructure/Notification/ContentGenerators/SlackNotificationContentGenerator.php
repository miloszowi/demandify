<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\ContentGenerators;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Notification\NotificationContentGenerator;
use Twig\Environment;

class SlackNotificationContentGenerator implements NotificationContentGenerator
{
    public function __construct(private readonly Environment $twigEnvironment) {}

    /**
     * @param mixed[] $data
     */
    public function generate(string $template, array $data): string
    {
        return $this->twigEnvironment->render(
            \sprintf('notifications/slack/%s.html.twig', $template),
            $data
        );
    }

    public function generateAttachments(string $template, string $demandIdentifier): array
    {
        return match ($template) {
            self::NEW_DEMAND_TEMPLATE => [
                [
                    'text' => '',
                    'fallback' => 'Something went wrong',
                    'callback_id' => \sprintf('demand_decision__%s', $demandIdentifier),
                    'color' => '#3AA3E3',
                    'attachment_type' => 'default',
                    'actions' => [
                        [
                            'name' => 'decision',
                            'text' => 'Approve',
                            'style' => 'danger',
                            'type' => 'button',
                            'value' => 'approve',
                            'confirm' => [
                                'title' => 'Task approval danger',
                                'text' => 'Are you sure? This task will be executed upon this approval.',
                            ],
                            'ok_text' => 'Yes',
                            'dismiss_text' => 'No',
                        ],
                        [
                            'name' => 'decision',
                            'text' => 'Decline',
                            'type' => 'button',
                            'value' => 'decline',
                        ],
                    ],
                ],
            ]
        };
    }

    public function supports(UserSocialAccountType $userSocialAccountType): bool
    {
        return $userSocialAccountType->isEqualTo(
            UserSocialAccountType::SLACK
        );
    }
}
