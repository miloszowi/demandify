<?php

declare(strict_types=1);

namespace Demandify\Domain\Demand;

use Demandify\Domain\Demand\Exception\InvalidDemandStatusException;

enum Status: string
{
    case NEW = 'NEW';
    case APPROVED = 'APPROVED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case FAILED = 'FAILED';
    case DECLINED = 'DECLINED';
    case EXECUTED = 'EXECUTED';

    /**
     * @var array<string[]>
     */
    private const array FLOW_TRANSITIONS = [
        self::NEW->value => [self::APPROVED->value, self::DECLINED->value],
        self::APPROVED->value => [self::IN_PROGRESS->value],
        self::IN_PROGRESS->value => [self::FAILED->value, self::EXECUTED->value],
    ];

    /**
     * @return string[]
     */
    public static function asArray(): array
    {
        return array_column(self::cases(), 'name');
    }

    public function isEqualTo(Status $status): bool
    {
        return $status->value === $this->value;
    }

    public function progress(Status $status): self
    {
        if (!\array_key_exists($this->value, self::FLOW_TRANSITIONS)) {
            throw new InvalidDemandStatusException(\sprintf(
                'No transitions allowed from status %s.',
                $this->value
            ));
        }

        if (!\in_array($status->value, self::FLOW_TRANSITIONS[$this->value], true)) {
            throw new InvalidDemandStatusException(\sprintf(
                'Invalid transition from status %s to %s.',
                $this->value,
                $status->value
            ));
        }

        return $status;
    }

    public function isInApprovedFlow(): bool
    {
        return \in_array(
            $this,
            [self::APPROVED, self::IN_PROGRESS, self::EXECUTED, self::FAILED],
            true
        );
    }

    public function isDeclined(): bool
    {
        return $this->isEqualTo(self::DECLINED);
    }
}
