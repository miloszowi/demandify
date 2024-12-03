<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Form\Demand;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DemandFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', type: ChoiceType::class, options: [
                'choices' => [
                    'test_app_1' => 'test',
                    'test_app_2' => 'test2',
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('content', options: [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('reason', options: [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Submit'])

        ;
    }

}