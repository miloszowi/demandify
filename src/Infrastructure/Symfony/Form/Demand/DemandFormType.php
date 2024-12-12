<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Symfony\Form\Demand;

use Querify\Domain\ExternalService\ExternalServiceRepository;
use Querify\Infrastructure\Symfony\Form\Validator\IsValidExternalService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemandFormType extends AbstractType
{
    public function __construct(private readonly ExternalServiceRepository $externalServiceRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $externalServiceChoices = [];

        foreach ($this->externalServiceRepository->getAll() as $externalService) {
            $externalServiceChoices[$externalService->name] = $externalService->name;
        }

        $builder
            ->add('service', type: ChoiceType::class, options: [
                'choices' => $externalServiceChoices,
                'constraints' => [
                    new NotBlank(),
                    new IsValidExternalService(),
                ],
            ])
            ->add('content', options: [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('reason', options: [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Submit'])
        ;
    }
}
