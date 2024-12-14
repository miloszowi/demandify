<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\ContentGenerator;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Exception\DomainLogicException;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Infrastructure\Notification\Client\NotificationClient;
use Twig\Environment;

class SlackNotificationContentGenerator
{
    public function __construct(private readonly Environment $twigEnvironment) {}

    public function generate(string $template, Demand $demand, UserSocialAccount $userSocialAccount): NotificationContentDTO
    {
        return new NotificationContentDTO(
            $this->twigEnvironment->render(
                \sprintf('notifications/slack/%s.html.twig', $template),
                [
                    'demand' => $demand,
                    'social_account' => $userSocialAccount,
                ]
            ),
            $this->generateAttachments($template, $demand, $userSocialAccount)
        );
    }

    /**
     * @return mixed[]
     */
    private function generateAttachments(string $template, Demand $demand, UserSocialAccount $userSocialAccount): array
    {
        return match ($template) {
            NotificationClient::NEW_DEMAND => [
                [
                    'text' => '',
                    'fallback' => 'Something went wrong',
                    'callback_id' => $demand->uuid->toString(),
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
            ],
            NotificationClient::DEMAND_APPROVED => [
                [
                    'text' => \sprintf(':white_check_mark: <@%s|cal> *approved* this.', $userSocialAccount->externalId),
                ],
            ],
            NotificationClient::DEMAND_DECLINED => [
                [
                    'text' => \sprintf(' :no_entry: <@%s|cal> *declined* this.', $userSocialAccount->externalId),
                ],
            ],
            default => throw new DomainLogicException('No suited action for given template') // todo
        };
    }
}
