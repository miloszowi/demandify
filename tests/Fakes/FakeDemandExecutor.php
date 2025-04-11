<?php

declare(strict_types=1);

namespace Demandify\Tests\Fakes;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Task\DemandExecutor;
use Demandify\Domain\Task\Task;

class FakeDemandExecutor implements DemandExecutor
{
    public function execute(Demand $demand): Task
    {
        if (str_contains($demand->content, 'failed')) {
            return new Task(
                false,
                1,
                'some error message'
            );
        }

        if (str_contains($demand->content, 'success')) {
            return new Task(
                true,
                1,
                'some success message',
                'result path'
            );
        }

        return new Task(
            false,
            1,
            'some error message'
        );
    }
}
