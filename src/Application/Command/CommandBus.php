<?php

declare(strict_types=1);

namespace Demandify\Application\Command;

interface CommandBus
{
    public function dispatch(Command $command): void;
}
