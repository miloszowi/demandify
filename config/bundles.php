<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use SymfonyCasts\Bundle\VerifyEmail\SymfonyCastsVerifyEmailBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
    FrameworkBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    MakerBundle::class => ['dev' => true],
    SecurityBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    SymfonyCastsVerifyEmailBundle::class => ['all' => true],
    NelmioApiDocBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
];
