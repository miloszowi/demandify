<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Task\FileHandler;

use Demandify\Domain\Demand\Demand;
use Demandify\Infrastructure\Task\Adapter\AdapterResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class FileHandler
{
    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire(env: 'APP_RESULTS_PATH')]
        private readonly string $path
    ) {}

    public function save(Demand $demand, AdapterResult $adapterResult): string
    {
        $path = \sprintf('%s/%s.csv', $this->path, $demand->uuid->toString());
        if ($this->filesystem->exists($path)) {
            throw new \Exception('File already exists'); // tood better exception
        }

        $content = $demand->content.PHP_EOL;
        $content .= 'Execution time: '.$adapterResult->executionTime.'ms'.PHP_EOL;
        $content .= 'Rows: '.$adapterResult->rowCount.PHP_EOL;
        if (!empty($adapterResult->columnNames)) {
            $content .= implode(',', $adapterResult->columnNames).PHP_EOL;
        }
        foreach ($adapterResult->data as $row) {
            if (!empty($row)) {
                $content .= implode(',', $row).PHP_EOL;
            }
        }
        $content .= PHP_EOL;

        $this->filesystem->dumpFile($path, $content);

        return $path;
    }
}
