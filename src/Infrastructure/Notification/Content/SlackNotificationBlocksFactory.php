<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Notification\Content;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Notification\Client\SlackNotificationClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SlackNotificationBlocksFactory
{
    public function __construct(
        #[Autowire(env: 'APP_URL')]
        private string $appUrl,
    ) {}

    /**
     * @return mixed[]
     */
    public function create(NotificationType $notificationType, Demand $demand): array
    {
        return match ($notificationType) {
            NotificationType::NEW_DEMAND => $this->createForNewDemand($demand),
            NotificationType::DEMAND_DECLINED => $this->createForDeclinedDemand($demand),
            NotificationType::DEMAND_APPROVED => $this->createForApprovedDemand($demand),
            NotificationType::TASK_SUCCEEDED => $this->createForTaskSucceeded($demand),
            NotificationType::TASK_FAILED => $this->createForTaskFailed($demand),
        };
    }

    /**
     * @return mixed[]
     */
    public function createForNewDemand(Demand $demand): array
    {
        $slackRequesterId = $demand->requester->getSocialAccount(UserSocialAccountType::SLACK)?->externalId;

        $fromText = $slackRequesterId
            ? \sprintf('<@%s|cal> (%s)', $slackRequesterId, $demand->requester->email)
            : $demand->requester->email;

        return [
            [
                'type' => 'divider',
            ],
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => ':new: New Demand',
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*From:*\n {$fromText}",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Destination:*\n`{$demand->service}`",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Reason:*\n{$demand->reason}",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Link:*\n <{$this->appUrl}/demands/{$demand->uuid->toString()}|View Demand>",
                    ],
                ],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "Content:\n```{$demand->content}```",
                ],
            ],
            [
                'type' => 'actions',
                'block_id' => $demand->uuid->toString(),
                'elements' => [
                    [
                        'type' => 'button',
                        'text' => [
                            'type' => 'plain_text',
                            'emoji' => true,
                            'text' => 'Approve',
                        ],
                        'style' => 'primary',
                        'value' => SlackNotificationClient::APPROVE_CALLBACK_KEY,
                    ],
                    [
                        'type' => 'button',
                        'text' => [
                            'type' => 'plain_text',
                            'emoji' => true,
                            'text' => 'Deny',
                        ],
                        'style' => 'danger',
                        'value' => SlackNotificationClient::DECLINE_CALLBACK_KEY,
                    ],
                ],
            ],
            [
                'type' => 'divider',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function createForApprovedDemand(Demand $demand): array
    {
        $approverSlackId = $demand->approver->getSocialAccount(UserSocialAccountType::SLACK)?->externalId;

        $approver = $approverSlackId
            ? \sprintf('<@%s|cal> (%s)', $approverSlackId, $demand->approver->email)
            : $demand->approver->email;

        $content = substr($demand->content, 0, 100);

        return [
            [
                'type' => 'divider',
            ],
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => ':white_check_mark: Demand Approved',
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "{$approver} approved your demand.",
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Content:*\n```{$content}…```",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Link:*\n <{$this->appUrl}/demands/{$demand->uuid->toString()}|View Demand>",
                    ],
                ],
            ],
            [
                'type' => 'divider',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function createForDeclinedDemand(Demand $demand): array
    {
        $approverSlackId = $demand->approver->getSocialAccount(UserSocialAccountType::SLACK)?->externalId;

        $approver = $approverSlackId
            ? \sprintf('<@%s|cal> (%s)', $approverSlackId, $demand->approver->email)
            : $demand->approver->email;

        $content = substr($demand->content, 0, 100);

        return [
            [
                'type' => 'divider',
            ],
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => ':no_entry: Demand Declined',
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "{$approver} declined your demand.",
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Content:*\n```{$content}…```",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Link:*\n <{$this->appUrl}/demands/{$demand->uuid->toString()}|View Demand>",
                    ],
                ],
            ],
            [
                'type' => 'divider',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function createForTaskSucceeded(Demand $demand): array
    {
        $content = substr($demand->content, 0, 100);

        return [
            [
                'type' => 'divider',
            ],
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => ':white_check_mark: Demand\'s Task Succeeded',
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Content:*\n```{$content}…```",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Link:*\n <{$this->appUrl}/demands/{$demand->uuid->toString()}|View Demand>",
                    ],
                ],
            ],
            [
                'type' => 'divider',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function createForTaskFailed(Demand $demand): array
    {
        $content = substr($demand->content, 0, 100);

        return [
            [
                'type' => 'divider',
            ],
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => ':no_entry: Demand\'s Task Failed',
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Content:*\n```{$content}…```",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Link:*\n <{$this->appUrl}/demands/{$demand->uuid->toString()}|View Demand>",
                    ],
                ],
            ],
            [
                'type' => 'divider',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function createForUpdatedDecision(Demand $demand): array
    {
        $blocks = $this->createForNewDemand($demand);
        $slackApproverId = $demand->approver->getSocialAccount(UserSocialAccountType::SLACK)?->externalId;
        $approver = $slackApproverId
            ? \sprintf('<@%s|cal> (%s)', $slackApproverId, $demand->approver->email)
            : $demand->approver->email;

        $decisionText = match ($demand->status->isDeclined()) {
            true => ":no_entry: declined by {$approver}",
            false => ":white_check_mark: approved by {$approver} ",
        };

        $blocks[4] = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $decisionText,
            ],
        ];

        return $blocks;
    }
}
