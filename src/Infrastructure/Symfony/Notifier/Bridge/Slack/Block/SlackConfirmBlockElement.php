<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Notifier\Bridge\Slack\Block;

use Symfony\Component\Notifier\Bridge\Slack\Block\AbstractSlackBlock;

class SlackConfirmBlockElement extends AbstractSlackBlock
{
    public function __construct(
        string $title,
        string $text,
        string $confirm,
        string $deny,
        ?string $style = null,
    ) {
        $this->options = [
            'title' => [
                'type' => 'plain_text',
                'text' => $title,
            ],
            'text' => [
                'type' => 'plain_text',
                'text' => $text,
            ],
            'confirm' => [
                'type' => 'plain_text',
                'text' => $confirm,
            ],
            'deny' => [
                'type' => 'plain_text',
                'text' => $deny,
            ],
        ];

        if ($style) {
            $this->options['style'] = $style;
        }
    }
}
