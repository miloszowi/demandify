<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DemandProcessor implements ProcessorInterface
{
    public function __construct(private readonly RequestStack $request) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        var_dump($this->request->getCurrentRequest()->getUser());
        var_dump($data);

        exit;
        // TODO: Implement process() method.
    }
}
