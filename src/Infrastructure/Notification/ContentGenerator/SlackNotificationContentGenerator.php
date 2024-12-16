<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\ContentGenerator;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\Status;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\User\User;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Notification\Client\SlackNotificationClient;
use Twig\Environment;

class SlackNotificationContentGenerator
{
    public function __construct(private readonly Environment $twigEnvironment) {}

    public function generate(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): NotificationContentDTO
    {
        $requesterSocialAccount = $demand->requester->getSocialAccount(UserSocialAccountType::SLACK);
        $approverSocialAccount = $demand->approver?->getSocialAccount(UserSocialAccountType::SLACK);

        return new NotificationContentDTO(
            $this->twigEnvironment->render(
                \sprintf('notifications/slack/%s.html.twig', $notificationType->value),
                [
                    'demand' => $demand,
                    'requester_social_account' => $requesterSocialAccount,
                    'approver_social_account' => $approverSocialAccount,
                ]
            ),
            $this->generateAttachments($notificationType, $demand),
            $userSocialAccount->externalId,
        );
    }

    public function generateDecisionUpdateAttachment(User $approver, Status $status): array
    {
        $content = $this->twigEnvironment->render(
            'notifications/slack/new_demand_update.html.twig',
            [
                'approver' => $approver,
                'approver_social_account' => $approver->getSocialAccount(UserSocialAccountType::SLACK),
                'status' => $status,
            ]
        );

        return [
            [
                'text' => $content,
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    private function generateAttachments(NotificationType $type, Demand $demand): array
    {
        return match ($type) {
            NotificationType::NEW_DEMAND => [
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
                            'value' => SlackNotificationClient::APPROVE_CALLBACK_KEY,
                            'confirm' => [
                                'title' => 'Action approval danger',
                                'text' => 'Are you sure? This action will be executed upon this approval.',
                            ],
                            'ok_text' => 'Yes',
                            'dismiss_text' => 'No',
                        ],
                        [
                            'name' => 'decision',
                            'text' => 'Decline',
                            'type' => 'button',
                            'value' => SlackNotificationClient::DECLINE_CALLBACK_KEY,
                        ],
                    ],
                ],
            ],
            NotificationType::DEMAND_APPROVED, NotificationType::DEMAND_DECLINED => [],
        };
    }
}
