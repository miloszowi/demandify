<?php

declare(strict_types=1);


namespace Demandify\Infrastructure;

use Demandify\Domain\Notification\NotificationType;
use Demandify\Infrastructure\Notification\Client\SlackNotificationClient;
use Demandify\Infrastructure\Symfony\Notifier\Bridge\Slack\Block\SlackActionsBlock;
use Demandify\Infrastructure\Symfony\Notifier\DemandNotificationSubject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackHeaderBlock;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
use Symfony\Component\Notifier\Bridge\Slack\UpdateMessageSlackOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

#[AsCommand(
    name: 'test:console',
    description: 'Test console command',
)]
class TestConsole extends Command
{
    public function __construct(
        private ChatterInterface $chatter,
        ?string $name = null
    )
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello world!');
        $id = Uuid::uuid4()->toString();

        $chatMessage = new ChatMessage('test');
        $actions = (new SlackActionsBlock())
            ->id($id)
            ->button('Approve', SlackNotificationClient::APPROVE_CALLBACK_KEY, style: 'primary')
            ->button('Decline', SlackNotificationClient::DECLINE_CALLBACK_KEY, style: 'danger')
        ;
        $options = (new SlackOptions())
            ->recipient('D08JFDULDL7')
            ->block(new SlackHeaderBlock('test'))
            ->block((new SlackSectionBlock())->text('my block'))
            ->block(new SlackDividerBlock())
            ->block(
                (new SlackSectionBlock())
                ->field('*Max rating*')
                ->field('5.0')
                ->field('*Min rating*')
                ->field('1.0')
            )
            ->block(
                $actions
            )
        ;
        dd($options->toArray());

        $options = new UpdateMessageSlackOptions('test', 'test', $options->toArray())
            ->block(
                new SlackActionsBlock()->id($id)
                ->button('XD', SlackNotificationClient::APPROVE_CALLBACK_KEY, style: 'primary')
            );


        $chatMessage->options($options)->subject(
            (string) new DemandNotificationSubject(Uuid::fromString('2adb9cb6-ff92-4270-b4da-aff7aef55815'), NotificationType::NEW_DEMAND)

        );

        $response = $this->chatter->send($chatMessage);

        return 1;
    }
}