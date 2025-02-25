<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Console;

use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Task\DemandExecutor;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteDemand extends Command
{
    public function __construct(private readonly DemandExecutor $demandExecutor, private readonly DemandRepository $demandRepository)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('demand:execute')
            ->setDescription('Executes demand.')
            ->addArgument('demandUuid', InputArgument::REQUIRED, 'Demand UUID')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $t = 1;
        $demand = $this->demandRepository->findByUuid(Uuid::fromString($input->getArgument('demandUuid')));

        $this->demandExecutor->execute($demand);

        return Command::SUCCESS;
    }
}
