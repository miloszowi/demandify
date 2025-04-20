<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Notifier\Bridge\Slack\Block;

use Symfony\Component\Notifier\Bridge\Slack\Block\AbstractSlackBlock;

/**
 * core SlackButtonBlockElement does require url, which is not required in slack API.
 */
class SlackButtonBlockElement extends AbstractSlackBlock
{
    public function __construct(
        string $text,
        ?string $value = null,
        ?string $url = null,
        ?string $style = null,
        ?SlackConfirmBlockElement $confirm = null,
    ) {
        $this->options = [
            'type' => 'button',
            'text' => [
                'type' => 'plain_text',
                'text' => $text,
            ],
        ];

        if ($value) {
            $this->options['value'] = $value;
        }

        if ($url) {
            $this->options['url'] = $url;
        }

        if ($style) {
            $this->options['style'] = $style;
        }

        if ($confirm) {
            $this->options['confirm'] = $confirm->toArray();
        }
    }
}
