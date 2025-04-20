<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Notification\Options;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Symfony\Notifier\Bridge\Slack\Block\SlackActionsBlock;
use Demandify\Infrastructure\Symfony\Notifier\Bridge\Slack\Block\SlackConfirmBlockElement;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackHeaderBlock;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
use Symfony\Component\Notifier\Bridge\Slack\UpdateMessageSlackOptions;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

class SlackNotificationOptionsFactory implements NotificationOptionsFactory
{
    public function __construct(
        #[Autowire(env: 'APP_URL')]
        private readonly string $appUrl
    ) {}

    public function supports(UserSocialAccountType $userSocialAccountType): bool
    {
        return $userSocialAccountType->isEqualTo(UserSocialAccountType::SLACK);
    }

    public function create(Demand $demand, NotificationType $notificationType, UserSocialAccount $userSocialAccount): MessageOptionsInterface
    {
        return match ($notificationType) {
            NotificationType::NEW_DEMAND => $this->newDemand($demand, $userSocialAccount),
            NotificationType::DEMAND_DECLINED => $this->declinedDemand($demand, $userSocialAccount),
            NotificationType::DEMAND_APPROVED => $this->approvedDemand($demand, $userSocialAccount),
            NotificationType::TASK_SUCCEEDED => $this->taskSucceeded($demand, $userSocialAccount),
            NotificationType::TASK_FAILED => $this->taskFailed($demand, $userSocialAccount),
            default => throw new \RuntimeException('Unknown notification type'),
        };
    }

    public function createForDecision(Notification $notification, User $approver, Status $status): MessageOptionsInterface
    {
        $options = $notification->options;
        $slackApproverId = $this->getUserTag($approver);
        $decisionText = match ($status->isDeclined()) {
            true => ":no_entry: declined by {$slackApproverId}",
            false => ":white_check_mark: approved by {$slackApproverId} ",
        };

        // todo fix this
        $options['blocks'][4] = new SlackSectionBlock()
            ->text($decisionText)
            ->toArray()
        ;

        return new UpdateMessageSlackOptions(
            $notification->recipient,
            $notification->notificationIdentifier,
            $options
        );
    }

    private function newDemand(Demand $demand, UserSocialAccount $recipient): SlackOptions
    {
        return new SlackOptions()
            ->recipient($recipient->externalId)
            ->block(new SlackDividerBlock())
            ->block(new SlackHeaderBlock(':new: New Demand'))
            ->block(
                new SlackSectionBlock()
                    ->field("*From:* {$this->getUserTag($demand->requester)}")
                    ->field("*Destination:* {$demand->service}")
                    ->field("*Reason:* {$demand->reason}")
                    ->field("*Link:* {$this->getDemandLink($demand)}")
            )
            ->block(
                new SlackSectionBlock()
                    ->text("Content:\n```{$demand->content}```")
            )
            ->block(
                new SlackActionsBlock()
                    ->id($demand->uuid->toString())
                    ->button(
                        'Approve',
                        self::APPROVE_CALLBACK_KEY,
                        style: 'primary',
                        confirm: new SlackConfirmBlockElement(
                            'Are you sure?',
                            'This action cannot be undone and will execute the demand.',
                            'Approve',
                            'Cancel',
                        )
                    )
                    ->button('Decline', self::DECLINE_CALLBACK_KEY, style: 'danger')
            )
            ->block(new SlackDividerBlock())
        ;
    }

    private function approvedDemand(Demand $demand, UserSocialAccount $recipient): SlackOptions
    {
        return new SlackOptions()
            ->recipient($recipient->externalId)
            ->block(new SlackDividerBlock())
            ->block(new SlackHeaderBlock(':white_check_mark: Demand Approved'))
            ->block(
                new SlackSectionBlock()
                    ->text("{$this->getUserTag($demand->approver)} approved your demand.")
            )
            ->block($this->getSummaryBlock($demand))
            ->block(new SlackDividerBlock())
        ;
    }

    private function declinedDemand(Demand $demand, UserSocialAccount $recipient): SlackOptions
    {
        return new SlackOptions()
            ->recipient($recipient->externalId)
            ->block(new SlackDividerBlock())
            ->block(new SlackHeaderBlock(':no_entry: Demand Declined'))
            ->block(
                new SlackSectionBlock()
                    ->text("{$this->getUserTag($demand->approver)} declined your demand.")
            )
            ->block($this->getSummaryBlock($demand))
            ->block(new SlackDividerBlock())
        ;
    }

    private function taskSucceeded(Demand $demand, UserSocialAccount $recipient): SlackOptions
    {
        return new SlackOptions()
            ->recipient($recipient->externalId)
            ->block(new SlackDividerBlock())
            ->block(new SlackHeaderBlock(':white_check_mark: Demand\'s Task Succeeded'))
            ->block($this->getSummaryBlock($demand))
            ->block(new SlackDividerBlock())
        ;
    }

    private function taskFailed(Demand $demand, UserSocialAccount $recipient): SlackOptions
    {
        return new SlackOptions()
            ->recipient($recipient->externalId)
            ->block(new SlackDividerBlock())
            ->block(new SlackHeaderBlock(':no_entry: Demand\'s Task Failed'))
            ->block($this->getSummaryBlock($demand))
            ->block(new SlackDividerBlock())
        ;
    }

    private function getUserTag(User $user): string
    {
        $slackId = $user->getSocialAccount(UserSocialAccountType::SLACK)?->externalId;

        return $slackId
            ? \sprintf('<@%s|cal> (%s)', $slackId, $user->email)
            : (string) $user->email;
    }

    private function getSummaryBlock(Demand $demand): SlackSectionBlock
    {
        $content = substr($demand->content, 0, 100);

        return new SlackSectionBlock()
            ->field("*Content:*\n```{$content}…```")
            ->field("*Link:* {$this->getDemandLink($demand)}")
        ;
    }

    private function getDemandLink(Demand $demand): string
    {
        return \sprintf(
            '<%s/demands/%s|View Demand>',
            $this->appUrl,
            $demand->uuid->toString()
        );
    }
}
