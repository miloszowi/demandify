<?php

declare(strict_types=1);

namespace Demandify\Tests\Doubles\Fakes;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Task\DemandExecutor;
use Demandify\Domain\Task\Task;

class FakeDemandExecutor implements DemandExecutor
{
    public const FILE_PATH = 'var/results/some_file.txt';

    public function execute(Demand $demand): Task
    {
        if (str_contains($demand->content, 'failed')) {
            return self::getFailedResult();
        }

        if (str_contains($demand->content, 'success')) {
            return self::getSuccessResult();
        }

        return self::getFailedResult();
    }

    public static function getFailedResult(): Task
    {
        return new Task(
            false,
            1,
            'some error message'
        );
    }

    public static function getSuccessResult(): Task
    {
        return new Task(
            true,
            1,
            'some success message',
            self::FILE_PATH
        );
    }
}
