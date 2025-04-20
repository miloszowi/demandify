<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Notifier\Bridge\Slack\Block;

use Symfony\Component\Notifier\Bridge\Slack\Block\AbstractSlackBlock;

/**
 * core SlackActionsBlock class does not provide setting block_id and value for callbacks.
 */
class SlackActionsBlock extends AbstractSlackBlock
{
    public function __construct()
    {
        $this->options['type'] = 'actions';
    }

    /**
     * @return $this
     */
    public function button(
        string $text,
        ?string $value = null,
        ?string $url = null,
        ?string $style = null,
        ?SlackConfirmBlockElement $confirm = null,
    ): static {
        if (25 === \count($this->options['elements'] ?? [])) {
            throw new \LogicException('Maximum number of buttons should not exceed 25.');
        }

        $element = new SlackButtonBlockElement($text, $value, $url, $style, $confirm);

        $this->options['elements'][] = $element->toArray();

        return $this;
    }

    public function id(string $id): static
    {
        $this->options['block_id'] = $id;

        return $this;
    }
}
