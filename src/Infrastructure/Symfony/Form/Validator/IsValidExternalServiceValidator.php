<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Symfony\Form\Validator;

use Querify\Domain\ExternalService\Exception\ExternalServiceNotFoundException;
use Querify\Domain\ExternalService\ExternalServiceRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsValidExternalServiceValidator extends ConstraintValidator
{
    public function __construct(private readonly ExternalServiceRepository $externalServiceRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsValidExternalService) {
            throw new UnexpectedTypeException($constraint, IsValidExternalService::class);
        }

        try {
            $this->externalServiceRepository->getByName($value);
        } catch (ExternalServiceNotFoundException) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(IsValidExternalService::IS_VALID_EXTERNAL_SERVICE_ERROR)
                ->addViolation()
            ;
        }
    }
}
