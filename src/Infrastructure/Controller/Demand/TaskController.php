<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller\Demand;

use Demandify\Domain\Demand\Demand;
use Demandify\Infrastructure\Authentication\Voter\DemandVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TaskController extends AbstractController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $projectDir
    ) {}

    #[Route(
        path: '/demand/{id}/task',
        name: 'app_demand_task_view',
        requirements: ['id' => Requirement::UUID],
        methods: [Request::METHOD_GET],
    )]
    #[IsGranted(DemandVoter::VIEW, subject: 'demand')]
    public function taskResult(Demand $demand): Response
    {
        return new BinaryFileResponse($this->getTaskPath($demand));
    }

    #[Route(
        path: '/demand/{id}/task/download',
        name: 'app_demand_task_download',
        requirements: ['id' => Requirement::UUID],
        methods: [Request::METHOD_GET],
    )]
    #[IsGranted(DemandVoter::VIEW, subject: 'demand')]
    public function taskDownload(Demand $demand): BinaryFileResponse
    {
        return $this->file(
            new File($this->getTaskPath($demand))
        );
    }

    public function getTaskPath(Demand $demand): string
    {
        if (null === $demand->task || null === $demand->task->resultPath) {
            throw new NotFoundHttpException();
        }

        $path = \sprintf(
            '%s/%s',
            $this->projectDir,
            $demand->task->resultPath
        );

        if (false === $this->filesystem->exists($path)) {
            throw new NotFoundHttpException();
        }

        return $path;
    }
}
